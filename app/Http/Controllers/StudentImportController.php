<?php

namespace App\Http\Controllers;

use App\Imports\StudentReviewImport;
use App\Models\ReviewStudent;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\Subject;
use App\Traits\ActivityManagementTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    use ActivityManagementTrait;
    /**
     * Show the upload form and review pending imported students.
     */
    public function showUploadForm()
    {
        return redirect()->route('instructor.students.index', ['tab' => 'import']);
    }

/**
 * Handle Excel upload and store to review table.
 */
public function upload(Request $request)
{
    $request->validate([
        'file'       => 'required|file|mimes:xlsx,xls|max:5120',
        'list_name'  => 'nullable|string|max:255',
    ], [
        'file.required' => 'Please choose an Excel file to upload.',
        'file.file' => 'The selected upload is not a valid file.',
        'file.mimes' => 'Please upload a valid Excel file in .xlsx or .xls format.',
        'file.max' => 'The Excel file is too large. Please keep it under 5 MB.',
        'list_name.max' => 'The uploaded list name is too long.',
    ]);

    $listName = trim((string) ($request->list_name ?? pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME)));
    $redirectParams = $this->importRedirectParams($request, $listName);

    // Check if list_name already exists
    $exists = ReviewStudent::where('list_name', $listName)
        ->where('instructor_id', Auth::id())
        ->exists();

    if ($exists) {
        return $this->uploadFailureResponse(
            $request,
            ["A file with the name '{$listName}' already exists. Please rename the file or remove the old upload first."],
            $redirectParams
        );
    }

    try {
        $import = new StudentReviewImport(null, $listName);

        Excel::import($import, $request->file('file'));

        if ($import->importedCount() < 1) {
            throw LaravelValidationException::withMessages([
                'file' => 'No valid student rows were found in the uploaded Excel file.',
            ]);
        }
    } catch (LaravelValidationException $exception) {
        return $this->uploadFailureResponse(
            $request,
            $exception->errors()['file'] ?? [$exception->getMessage()],
            $redirectParams
        );
    } catch (NoTypeDetectedException $exception) {
        return $this->uploadFailureResponse(
            $request,
            ['Invalid Excel file. Please upload a real .xlsx or .xls file using the student import template.'],
            $redirectParams
        );
    } catch (\Throwable $exception) {
        report($exception);

        return $this->uploadFailureResponse(
            $request,
            ['The uploaded Excel file could not be processed. Please check the file format and try again.'],
            $redirectParams
        );
    }

    if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Student list uploaded for review.',
            'redirect_url' => route('instructor.students.index', $redirectParams),
            'list_name' => $listName,
        ]);
    }

    return redirect()
        ->route('instructor.students.index', $redirectParams)
        ->with('status', 'Student list uploaded for review.');
}

    /**
     * Confirm reviewed students and move to main students table.
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'list_name'  => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
        ]);
    
        $academicPeriodId = session('active_academic_period_id');
        // Ensure the subject is accessible to this instructor and belongs to active academic period
        $subject = Subject::where('id', $request->subject_id)
            ->where('academic_period_id', $academicPeriodId)
            ->where(function($q) {
                $q->where('instructor_id', Auth::id())
                  ->orWhereHas('instructors', function($qr) { $qr->where('instructor_id', Auth::id()); });
            })
            ->firstOrFail();
    
        $listName = $request->list_name;
        $selectedIds = explode(',', $request->input('selected_student_ids'));
    
        $reviewStudents = ReviewStudent::where('instructor_id', Auth::id())
            ->where('list_name', $listName)
            ->whereIn('id', $selectedIds)
            ->get();
    
        foreach ($reviewStudents as $review) {
            // Check if a matching student already exists
            $existingStudent = Student::where('first_name', $review->first_name)
                ->where('last_name', $review->last_name)
                ->where('middle_name', $review->middle_name)
                ->where('year_level', $review->year_level)
                ->where('course_id', $review->course_id)
                ->where('academic_period_id', $subject->academic_period_id)
                ->first();
    
            if ($existingStudent) {
                // Check if already linked to this subject
                $alreadyEnrolled = StudentSubject::where('student_id', $existingStudent->id)
                    ->where('subject_id', $subject->id)
                    ->exists();
    
                if (!$alreadyEnrolled) {
                    StudentSubject::create([
                        'student_id' => $existingStudent->id,
                        'subject_id' => $subject->id,
                    ]);
                }
    
                continue; // Skip creating new Student
            }
    
            // Create new student
            $student = Student::create([
                'first_name'         => $review->first_name,
                'middle_name'        => $review->middle_name,
                'last_name'          => $review->last_name,
                'year_level'         => $review->year_level,
                'course_id'          => $review->course_id,
                'department_id'      => Auth::user()->department_id,
                'academic_period_id' => $subject->academic_period_id,
                'created_by'         => Auth::id(),
                'updated_by'         => Auth::id(),
            ]);
    
            StudentSubject::create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
            ]);
        }
    
        // Check and create activities for all terms if missing
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];
        foreach ($terms as $term) {
            $this->getOrCreateDefaultActivities($subject->id, $term);
        }
    
                // Mark review students as confirmed instead of deleting them
        ReviewStudent::where('instructor_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->update(['is_confirmed' => true]);

        return redirect()->route('instructor.students.index', ['tab' => 'import'])->with('status', 'Selected students successfully imported to the selected subject.');
    }
    

    /**
     * API: Get students enrolled in a specific subject (AJAX).
     */
    public function getSubjectStudents($subjectId)
    {
        $subject = Subject::with(['students' => function ($query) {
            $query->where('students.is_deleted', 0)->with('course');
        }])
        ->where('id', $subjectId)
        ->where(function($q) { // allow either primary instructor or pivot instructor
            $q->where('instructor_id', Auth::id())
              ->orWhereHas('instructors', function($q2) { $q2->where('instructor_id', Auth::id()); });
        })
        ->firstOrFail();

        $students = $subject->students->map(function ($student) {
            return [
                'full_name'   => $student->full_name,
                'course_code' => $student->course->course_code ?? 'N/A',
                'year_level'  => $student->formatted_year_level,
            ];
        });

        return response()->json($students);
    }

    protected function importRedirectParams(Request $request, ?string $listName = null): array
    {
        $params = ['tab' => 'import'];

        if ($listName) {
            $params['list_name'] = $listName;
        }

        if ($request->filled('compare_subject_id')) {
            $params['compare_subject_id'] = $request->input('compare_subject_id');
        }

        return $params;
    }

    protected function uploadFailureResponse(Request $request, array|string $messages, array $redirectParams, int $status = 422)
    {
        $messageList = collect(is_array($messages) ? $messages : [$messages])
            ->flatten()
            ->filter(fn ($message) => is_string($message) && trim($message) !== '')
            ->values();

        $primaryMessage = $messageList->first() ?? 'The Excel upload failed. Please review the file and try again.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $primaryMessage,
                'errors' => $messageList->all(),
            ], $status);
        }

        return redirect()
            ->route('instructor.students.index', $redirectParams)
            ->withErrors(['file' => $primaryMessage])
            ->with('import_error_details', $messageList->all())
            ->withInput();
    }
}
