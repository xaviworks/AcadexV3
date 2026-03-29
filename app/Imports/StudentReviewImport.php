<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\ReviewStudent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentReviewImport implements ToCollection
{
    protected $subjectId;
    protected $listName;
    protected int $importedCount = 0;

    public function __construct($subjectId, $listName)
    {
        $this->subjectId = $subjectId; // Can be null
        $this->listName = is_string($listName) ? $listName : 'Untitled List';
    }

    public function importedCount(): int
    {
        return $this->importedCount;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded Excel file is empty.',
            ]);
        }

        $headerRow = collect($rows->first() ?? []);
        $headerMap = $this->resolveHeaderMap($headerRow);
        $dataRows = $rows->slice(1)->values();

        if ($dataRows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded Excel file only contains headers. Add at least one student row and try again.',
            ]);
        }

        $courseLookup = Course::where('is_deleted', false)
            ->get(['id', 'course_code'])
            ->mapWithKeys(fn (Course $course) => [strtoupper(trim((string) $course->course_code)) => $course->id]);

        $pendingRecords = [];
        $errors = [];
        $timestamp = now();

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $cells = collect($row);

            if ($this->isRowEmpty($cells)) {
                continue;
            }

            $lastName = $this->stringValue($cells->get($headerMap['last_name']));
            $firstName = $this->stringValue($cells->get($headerMap['first_name']));
            $middleName = $headerMap['middle_name'] !== null
                ? $this->stringValue($cells->get($headerMap['middle_name']))
                : null;
            $courseCode = strtoupper($this->stringValue($cells->get($headerMap['course_code'])));
            $yearLevel = $this->parseYearLevel($cells->get($headerMap['year_level']));

            $rowErrors = [];

            if ($lastName === '') {
                $rowErrors[] = 'Last Name is required';
            }

            if ($firstName === '') {
                $rowErrors[] = 'First Name is required';
            }

            if ($courseCode === '') {
                $rowErrors[] = 'Course Code is required';
            } elseif (! $courseLookup->has($courseCode)) {
                $rowErrors[] = "Course Code '{$courseCode}' does not exist";
            }

            if ($yearLevel === null) {
                $rowErrors[] = 'Year Level must be a number from 1 to 5';
            }

            if (! empty($rowErrors)) {
                $errors[] = "Row {$rowNumber}: " . implode(', ', $rowErrors) . '.';
                continue;
            }

            $pendingRecords[] = [
                'instructor_id' => Auth::id(),
                'list_name' => $this->listName,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_name' => $middleName ?: null,
                'year_level' => $yearLevel,
                'course_id' => $courseLookup->get($courseCode),
                'subject_id' => $this->subjectId,
                'is_confirmed' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages([
                'file' => $this->summarizeErrors($errors),
            ]);
        }

        if (empty($pendingRecords)) {
            throw ValidationException::withMessages([
                'file' => 'No valid student rows were found in the uploaded Excel file.',
            ]);
        }

        ReviewStudent::insert($pendingRecords);
        $this->importedCount = count($pendingRecords);
    }

    protected function resolveHeaderMap(Collection $headerRow): array
    {
        $normalizedHeaderMap = $headerRow
            ->map(fn ($value) => $this->normalizeHeading($value))
            ->toArray();

        $requiredColumns = [
            'last_name' => ['lastname', 'surname', 'familyname'],
            'first_name' => ['firstname', 'givenname'],
            'middle_name' => ['middlename', 'middleinitial', 'mi'],
            'year_level' => ['yearlevel', 'year', 'yrlevel'],
            'course_code' => ['coursecode', 'programcode', 'course', 'program'],
        ];

        $resolved = [];
        $missing = [];

        foreach ($requiredColumns as $field => $aliases) {
            $index = null;

            foreach ($aliases as $alias) {
                $foundIndex = array_search($alias, $normalizedHeaderMap, true);
                if ($foundIndex !== false) {
                    $index = $foundIndex;
                    break;
                }
            }

            if ($field !== 'middle_name' && $index === null) {
                $missing[] = match ($field) {
                    'last_name' => 'Last Name',
                    'first_name' => 'First Name',
                    'year_level' => 'Year Level',
                    'course_code' => 'Course Code',
                    default => $field,
                };
            }

            $resolved[$field] = $index;
        }

        if (! empty($missing)) {
            throw ValidationException::withMessages([
                'file' => 'Invalid Excel format. The header row must include: Last Name, First Name, Middle Name, Year Level, and Course Code.',
            ]);
        }

        return $resolved;
    }

    protected function normalizeHeading($value): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim((string) $value)));
    }

    protected function stringValue($value): string
    {
        return trim((string) $value);
    }

    protected function parseYearLevel($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = trim((string) $value);

        if (preg_match('/([1-5])/', $normalized, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function isRowEmpty(Collection $row): bool
    {
        return $row->every(fn ($cell) => trim((string) $cell) === '');
    }

    protected function summarizeErrors(array $errors): array
    {
        $visibleErrors = array_slice($errors, 0, 5);

        if (count($errors) > 5) {
            $visibleErrors[] = 'Additional rows also have issues. Please review the file template and try again.';
        }

        return $visibleErrors;
    }
}
