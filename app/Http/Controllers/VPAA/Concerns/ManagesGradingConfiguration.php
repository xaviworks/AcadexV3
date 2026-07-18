<?php

namespace App\Http\Controllers\VPAA\Concerns;

use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Department;
use App\Models\FinalGrade;
use App\Models\GradesFormula;
use App\Models\Score;
use App\Models\Subject;
use App\Models\TermGrade;
use App\Services\GradesFormulaService;
use App\Support\Grades\FormulaDefaults;
use App\Support\Grades\FormulaStructure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ManagesGradingConfiguration
{
    protected function subjectHasRecordedGrades(Subject $subject, ?int $academicPeriodId = null): bool
    {
        $termGrades = TermGrade::where('subject_id', $subject->id)
            ->where('is_deleted', false)
            ->when($academicPeriodId, fn ($query, $periodId) => $query->where('academic_period_id', $periodId));

        if ($termGrades->exists()) {
            return true;
        }

        $finalGrades = FinalGrade::where('subject_id', $subject->id)
            ->where('is_deleted', false)
            ->when($academicPeriodId, fn ($query, $periodId) => $query->where('academic_period_id', $periodId));

        if ($finalGrades->exists()) {
            return true;
        }

        return Score::where('is_deleted', false)
            ->whereHas('activity', function ($query) use ($subject) {
                $query->where('subject_id', $subject->id)
                    ->where('is_deleted', false);
            })
            ->exists();
    }

    protected function resolveFormulaPeriodContext(): array
    {
        $periods = AcademicPeriod::orderBy('academic_year', 'desc')
            ->orderBy('semester')
            ->get();

        $academicYears = $periods->pluck('academic_year')->unique()->values();

        $requestedPeriodValue = request()->input('academic_period_id');
        $forceAllPeriods = $requestedPeriodValue === 'all';
        $requestedPeriodId = null;

        if (! $forceAllPeriods && $requestedPeriodValue !== null && $requestedPeriodValue !== '') {
            $requestedPeriodId = (int) $requestedPeriodValue;
        }

        $requestedYear = request()->input('academic_year');
        $requestedSemester = request()->filled('semester') ? request()->input('semester') : null;
        if ($requestedSemester === '') {
            $requestedSemester = null;
        }

        $selectedPeriod = null;

        if ($requestedPeriodId !== null) {
            $selectedPeriod = $periods->firstWhere('id', $requestedPeriodId);
            if ($selectedPeriod) {
                $requestedYear = $selectedPeriod->academic_year;
                $requestedSemester = $selectedPeriod->semester;
            }
        }

        if ($requestedYear && $requestedSemester) {
            $selectedPeriod = $periods->first(function (AcademicPeriod $period) use ($requestedYear, $requestedSemester) {
                return $period->academic_year === $requestedYear && $period->semester === $requestedSemester;
            });
        }

        if (! $selectedPeriod && $requestedYear) {
            $selectedPeriod = $periods->firstWhere('academic_year', $requestedYear);
            if (! $requestedSemester && $selectedPeriod) {
                $requestedSemester = $selectedPeriod->semester;
            }
        }

        if (! $selectedPeriod && $requestedSemester) {
            $selectedPeriod = $periods->firstWhere('semester', $requestedSemester);
        }

        if (! $selectedPeriod && ! $forceAllPeriods && session('active_academic_period_id')) {
            $selectedPeriod = $periods->firstWhere('id', (int) session('active_academic_period_id'));
        }

        if (! $selectedPeriod && ! $forceAllPeriods && $periods->isNotEmpty()) {
            $selectedPeriod = $periods->first();
        }

        $selectedAcademicYear = $requestedYear ?? $selectedPeriod?->academic_year;
        $selectedSemester = $requestedSemester ?? $selectedPeriod?->semester;
        $selectedAcademicPeriodId = $selectedPeriod?->id;

        if ($forceAllPeriods) {
            $selectedAcademicYear = null;
            $selectedSemester = null;
            $selectedAcademicPeriodId = null;
        } elseif ($selectedSemester === null) {
            $selectedAcademicPeriodId = null;
        } elseif (! $selectedAcademicPeriodId && $selectedAcademicYear) {
            $matchingPeriod = $periods->first(function (AcademicPeriod $period) use ($selectedAcademicYear, $selectedSemester) {
                return $period->academic_year === $selectedAcademicYear && $period->semester === $selectedSemester;
            });
            $selectedAcademicPeriodId = $matchingPeriod?->id;
        }

        $availableSemesters = $selectedAcademicYear
            ? $periods->where('academic_year', $selectedAcademicYear)->pluck('semester')->unique()->values()
            : $periods->pluck('semester')->unique()->values();

        return [
            'academic_periods' => $periods,
            'academic_years' => $academicYears,
            'academic_year' => $selectedAcademicYear,
            'semester' => $selectedSemester,
            'academic_period_id' => $selectedAcademicPeriodId,
            'available_semesters' => $availableSemesters,
        ];
    }

    protected function applyPeriodFilters($query, ?string $semester, ?int $academicPeriodId)
    {
        return $query
            ->when($academicPeriodId, function ($q) use ($academicPeriodId) {
                $q->where(function ($scoped) use ($academicPeriodId) {
                    $scoped->where('academic_period_id', $academicPeriodId)
                        ->orWhereNull('academic_period_id');
                });
            })
            ->when($semester, function ($q) use ($semester) {
                $q->where(function ($scoped) use ($semester) {
                    $scoped->where('semester', $semester)
                        ->orWhereNull('semester');
                });
            });
    }

    protected function generateFormulaName(
        string $scope,
        ?Department $department,
        ?Course $course,
        ?Subject $subject,
        ?int $academicPeriodId,
        ?string $semester
    ): string {
        $segments = [$scope];

        if ($department && $scope !== 'subject') {
            $segments[] = 'dept_'.$department->id;
        }

        if ($course && in_array($scope, ['course', 'subject'], true)) {
            $segments[] = 'course_'.$course->id;
        }

        if ($subject && $scope === 'subject') {
            $segments[] = 'subject_'.$subject->id;
        }

        if ($academicPeriodId !== null) {
            $segments[] = 'period_'.$academicPeriodId;
        }

        if ($semester !== null && $semester !== '') {
            $segments[] = 'sem_'.Str::slug($semester, '_');
        }

        $segments[] = Str::uuid()->toString();

        return implode('_', $segments);
    }

    protected function generateFallbackName(Department $department, ?string $semester, ?string $academicYear): string
    {
        $segments = [
            'department',
            $department->id,
            'fallback',
        ];

        if ($academicYear) {
            $segments[] = Str::slug($academicYear, '_');
        }

        if ($semester) {
            $segments[] = Str::slug($semester, '_');
        }

        return strtolower(implode('_', array_filter($segments)));
    }

    protected function formulaMatchesContext(?GradesFormula $formula, ?string $semester, ?int $academicPeriodId): bool
    {
        if (! $formula) {
            return false;
        }

        $semesterMatch = $semester === null
            ? $formula->semester === null
            : $formula->semester === $semester;

        $periodMatch = $academicPeriodId === null
            ? $formula->academic_period_id === null
            : (int) $formula->academic_period_id === (int) $academicPeriodId;

        return $semesterMatch && $periodMatch;
    }

    protected function formulaQueryParams(array $merge = []): array
    {
        $params = array_merge(
            request()->only(['semester', 'academic_year', 'academic_period_id']),
            $merge
        );

        return collect($params)
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();
    }

    protected function ensureDepartmentFallback(Department $department, ?array $periodContext = null): GradesFormula
    {
        $context = $periodContext ?? $this->resolveFormulaPeriodContext();
        $selectedSemester = $context['semester'] ?? null;
        $selectedAcademicPeriodId = $context['academic_period_id'] ?? null;
        $selectedAcademicYear = $context['academic_year'] ?? null;

        $baseQuery = GradesFormula::with('weights')
            ->where('department_id', $department->id)
            ->where('scope_level', 'department')
            ->where('is_department_fallback', true)
            ->orderByDesc('updated_at');

        if ($selectedAcademicPeriodId) {
            $specific = (clone $baseQuery)
                ->where('academic_period_id', $selectedAcademicPeriodId)
                ->when($selectedSemester, fn ($q, $sem) => $q->where('semester', $sem))
                ->first();
            if ($specific) {
                return $specific;
            }

            $periodFallback = (clone $baseQuery)
                ->where('academic_period_id', $selectedAcademicPeriodId)
                ->whereNull('semester')
                ->first();
            if ($periodFallback) {
                return $periodFallback;
            }
        }

        if ($selectedSemester) {
            $semesterFallback = (clone $baseQuery)
                ->whereNull('academic_period_id')
                ->where('semester', $selectedSemester)
                ->first();
            if ($semesterFallback) {
                return $semesterFallback;
            }
        }

        $genericFallback = (clone $baseQuery)
            ->whereNull('academic_period_id')
            ->whereNull('semester')
            ->first();
        if ($genericFallback) {
            return $genericFallback;
        }

        $label = trim(($department->department_description ?? 'Department').' Baseline Formula');
        if ($label === '') {
            $label = 'Department Baseline Formula';
        }

        $fallbackName = $this->generateFallbackName($department, $selectedSemester, $selectedAcademicYear);
        $semesterForInsert = $selectedSemester;
        $periodForInsert = $selectedAcademicPeriodId;

        // Guard: a row with this generated name may already exist but was missed by
        // the context-filtered queries above (e.g. is_department_fallback = false,
        // wrong scope_level, or non-null semester/period on an older record).
        // Promote and return it rather than attempting a duplicate insert.
        $existingByName = GradesFormula::with('weights')->where('name', $fallbackName)->first();
        if ($existingByName) {
            if (! $existingByName->is_department_fallback || $existingByName->scope_level !== 'department') {
                $existingByName->update([
                    'is_department_fallback' => true,
                    'scope_level' => 'department',
                    'department_id' => $department->id,
                ]);
                $existingByName->refresh();
            }
            GradesFormulaService::flushCache();

            return $existingByName->loadMissing('weights');
        }

        try {
            $fallback = DB::transaction(function () use ($department, $label, $fallbackName, $semesterForInsert, $periodForInsert) {
                $formula = GradesFormula::create([
                    'name' => $fallbackName,
                    'label' => $label,
                    'scope_level' => 'department',
                    'department_id' => $department->id,
                    'semester' => $semesterForInsert,
                    'academic_period_id' => $periodForInsert,
                    'base_score' => 40,
                    'scale_multiplier' => 60,
                    'passing_grade' => 75,
                    'is_department_fallback' => true,
                ]);

                $formula->weights()->createMany([
                    ['activity_type' => 'quiz', 'weight' => 0.40],
                    ['activity_type' => 'ocr', 'weight' => 0.20],
                    ['activity_type' => 'exam', 'weight' => 0.40],
                ]);

                return $formula;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Race-condition safety net: if a duplicate-key error occurs after the
            // name-based guard above (two concurrent requests), return the row that
            // won the insert race instead of surfacing a 500.
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')) {
                $fallback = GradesFormula::with('weights')->where('name', $fallbackName)->first();
                if ($fallback) {
                    GradesFormulaService::flushCache();

                    return $fallback;
                }
            }
            throw $e;
        }

        GradesFormulaService::flushCache();

        return $fallback->fresh('weights');
    }

    protected function formulasEquivalent(GradesFormula $first, GradesFormula $second): bool
    {
        $first->loadMissing('weights');
        $second->loadMissing('weights');

        $numericFieldsMatch = abs(($first->base_score ?? 0) - ($second->base_score ?? 0)) < 0.0001
            && abs(($first->scale_multiplier ?? 0) - ($second->scale_multiplier ?? 0)) < 0.0001
            && abs(($first->passing_grade ?? 0) - ($second->passing_grade ?? 0)) < 0.0001;

        if (! $numericFieldsMatch) {
            return false;
        }

        $firstWeights = collect($first->weights)
            ->mapWithKeys(fn ($weight) => [mb_strtolower($weight->activity_type) => round((float) $weight->weight, 4)])
            ->sortKeys();
        $secondWeights = collect($second->weights)
            ->mapWithKeys(fn ($weight) => [mb_strtolower($weight->activity_type) => round((float) $weight->weight, 4)])
            ->sortKeys();

        return $firstWeights->all() === $secondWeights->all();
    }

    protected function cloneFormulaToCourse(Course $course, GradesFormula $sourceFormula): GradesFormula
    {
        $sourceFormula->loadMissing('weights');

        $label = trim(($course->course_code ? $course->course_code.' - ' : '').($course->course_description ?? 'Course').' Formula');
        if ($label === '') {
            $label = 'Course Formula';
        }

        return DB::transaction(function () use ($course, $sourceFormula, $label) {
            $requestSemester = request('semester');
            $requestPeriodId = request('academic_period_id');

            $activePeriodId = null;
            if ($requestPeriodId !== null && $requestPeriodId !== '') {
                $activePeriodId = (int) $requestPeriodId;
            } elseif (session()->has('active_academic_period_id')) {
                $activePeriodId = (int) session('active_academic_period_id');
            }

            $periodModel = $activePeriodId ? AcademicPeriod::find($activePeriodId) : null;

            $selectedSemester = $requestSemester !== null && $requestSemester !== ''
                ? $requestSemester
                : ($periodModel?->semester ?? null);

            if ($selectedSemester === null && $periodModel) {
                $selectedSemester = $periodModel->semester;
            }

            $formula = GradesFormula::firstOrNew([
                'course_id' => $course->id,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
            ]);

            if (! $formula->exists) {
                $formula->name = $this->generateFormulaName('course', $course->department, $course, null, $activePeriodId, $selectedSemester);
                $formula->scope_level = 'course';
            }

            $formula->fill([
                'label' => $label,
                'scope_level' => 'course',
                'department_id' => $course->department_id,
                'subject_id' => null,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
                'base_score' => $sourceFormula->base_score,
                'scale_multiplier' => $sourceFormula->scale_multiplier,
                'passing_grade' => $sourceFormula->passing_grade,
                'structure_type' => $sourceFormula->structure_type,
                'structure_config' => $sourceFormula->structure_config,
                'is_department_fallback' => false,
            ]);

            $formula->save();

            $weights = $sourceFormula->weights
                ->map(fn ($weight) => [
                    'activity_type' => $weight->activity_type,
                    'weight' => (float) $weight->weight,
                ])
                ->values()
                ->all();

            $formula->weights()->delete();

            if (! empty($weights)) {
                $formula->weights()->createMany($weights);
            }

            return $formula->fresh('weights');
        });
    }

    protected function cloneFormulaToSubject(Subject $subject, GradesFormula $sourceFormula): GradesFormula
    {
        $sourceFormula->loadMissing('weights');

        $label = trim(($subject->subject_code ? $subject->subject_code.' - ' : '').($subject->subject_description ?? 'Subject').' Formula');
        if ($label === '') {
            $label = 'Subject Formula';
        }

        return DB::transaction(function () use ($subject, $sourceFormula, $label) {
            $requestSemester = request('semester');
            $requestPeriodId = request('academic_period_id');

            $activePeriodId = null;
            if ($requestPeriodId !== null && $requestPeriodId !== '') {
                $activePeriodId = (int) $requestPeriodId;
            } elseif (session()->has('active_academic_period_id')) {
                $activePeriodId = (int) session('active_academic_period_id');
            }

            $periodModel = $activePeriodId ? AcademicPeriod::find($activePeriodId) : null;

            $selectedSemester = $requestSemester !== null && $requestSemester !== ''
                ? $requestSemester
                : ($periodModel?->semester ?? null);

            if ($selectedSemester === null && $periodModel) {
                $selectedSemester = $periodModel->semester;
            }

            $formula = GradesFormula::firstOrNew([
                'subject_id' => $subject->id,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
            ]);

            if (! $formula->exists) {
                $formula->name = $this->generateFormulaName('subject', $subject->department, $subject->course, $subject, $activePeriodId, $selectedSemester);
                $formula->scope_level = 'subject';
            }

            $formula->fill([
                'label' => $label,
                'scope_level' => 'subject',
                'department_id' => $subject->department_id,
                'course_id' => null,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
                'base_score' => $sourceFormula->base_score,
                'scale_multiplier' => $sourceFormula->scale_multiplier,
                'passing_grade' => $sourceFormula->passing_grade,
                'structure_type' => $sourceFormula->structure_type,
                'structure_config' => $sourceFormula->structure_config,
                'is_department_fallback' => false,
            ]);

            $formula->save();

            $weights = $sourceFormula->weights
                ->map(fn ($weight) => [
                    'activity_type' => $weight->activity_type,
                    'weight' => (float) $weight->weight,
                ])
                ->values()
                ->all();

            $formula->weights()->delete();

            if (! empty($weights)) {
                $formula->weights()->createMany($weights);
            }

            return $formula->fresh('weights');
        });
    }

    protected function applyStructureTypeToSubject(Subject $subject, string $structureType, GradesFormula $baseline): GradesFormula
    {
        $structure = $this->resolveStructureConfigForKey($structureType);

        $weights = collect(FormulaStructure::flattenWeights($structure))
            ->map(function (array $entry) {
                $activityType = mb_strtolower($entry['activity_type'] ?? $entry['key'] ?? 'component');

                return [
                    'activity_type' => $activityType,
                    'weight' => (float) ($entry['weight'] ?? 0),
                ];
            })
            ->values()
            ->all();

        $label = trim(($subject->subject_code ? $subject->subject_code.' - ' : '').($subject->subject_description ?? 'Subject').' Formula');
        if ($label === '') {
            $label = 'Subject Formula';
        }

        return DB::transaction(function () use ($subject, $baseline, $label, $structureType, $structure, $weights) {
            $requestSemester = request('semester');
            $requestPeriodId = request('academic_period_id');

            $activePeriodId = null;
            if ($requestPeriodId !== null && $requestPeriodId !== '') {
                $activePeriodId = (int) $requestPeriodId;
            } elseif (session()->has('active_academic_period_id')) {
                $activePeriodId = (int) session('active_academic_period_id');
            }

            $periodModel = $activePeriodId ? AcademicPeriod::find($activePeriodId) : null;

            $selectedSemester = $requestSemester !== null && $requestSemester !== ''
                ? $requestSemester
                : ($periodModel?->semester ?? null);

            if ($selectedSemester === null && $periodModel) {
                $selectedSemester = $periodModel->semester;
            }

            $formula = GradesFormula::firstOrNew([
                'subject_id' => $subject->id,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
            ]);

            if (! $formula->exists) {
                $formula->name = $this->generateFormulaName('subject', $subject->department, $subject->course, $subject, $activePeriodId, $selectedSemester);
                $formula->scope_level = 'subject';
            }

            $formula->fill([
                'label' => $label,
                'scope_level' => 'subject',
                'department_id' => $subject->department_id,
                'course_id' => null,
                'semester' => $selectedSemester,
                'academic_period_id' => $activePeriodId,
                'base_score' => $baseline->base_score,
                'scale_multiplier' => $baseline->scale_multiplier,
                'passing_grade' => $baseline->passing_grade,
                'structure_type' => $structureType,
                'structure_config' => $structure,
                'is_department_fallback' => false,
            ]);

            $formula->save();

            $formula->weights()->delete();

            if (! empty($weights)) {
                $formula->weights()->createMany($weights);
            }

            return $formula->fresh('weights');
        });
    }

    protected function resetSubjectAssessmentsForNewStructure(Subject $subject): void
    {
        $actorId = Auth::id();

        DB::transaction(function () use ($subject, $actorId) {
            $activities = Activity::where('subject_id', $subject->id)
                ->where('is_deleted', false)
                ->get();

            if ($activities->isNotEmpty()) {
                $activityIds = $activities->pluck('id');

                Activity::whereIn('id', $activityIds)->update([
                    'is_deleted' => true,
                    'updated_by' => $actorId,
                ]);

                if ($activityIds->isNotEmpty()) {
                    Score::whereIn('activity_id', $activityIds)->update([
                        'is_deleted' => true,
                        'updated_by' => $actorId,
                    ]);
                }
            }

            TermGrade::where('subject_id', $subject->id)->delete();
            FinalGrade::where('subject_id', $subject->id)->delete();
        });
    }

    protected function getGlobalFormula(): GradesFormula
    {
        $formula = GradesFormula::with('weights')
            ->where('scope_level', 'global')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        if (! $formula) {
            $formula = GradesFormula::with('weights')
                ->whereNull('department_id')
                ->whereNull('course_id')
                ->whereNull('subject_id')
                ->orderByDesc('updated_at')
                ->orderByDesc('created_at')
                ->first();
        }

        if (! $formula) {
            $formula = new GradesFormula([
                'label' => FormulaDefaults::GLOBAL_FALLBACK_LABEL,
                'scope_level' => 'global',
                'base_score' => 40,
                'scale_multiplier' => 60,
                'passing_grade' => 75,
            ]);
            $formula->setRelation('weights', collect());
        }

        return $formula;
    }

    protected function prepareStructurePayload(GradesFormula $formula): array
    {
        $type = $formula->structure_type ?? 'lecture_only';
        $structure = $formula->structure_config ?? $this->resolveStructureConfigForKey($type);

        return [
            'type' => $type,
            'structure' => \App\Support\Grades\FormulaStructure::toPercentPayload($structure),
        ];
    }

    protected function prepareStructurePayloadFromOldInput(?string $type, ?string $payload): array
    {
        $type = $type ?: 'lecture_only';

        if ($payload) {
            try {
                $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return [
                        'type' => $type,
                        'structure' => $decoded,
                    ];
                }
            } catch (\Throwable $exception) {
                // Fallback to defaults when payload cannot be decoded.
            }
        }

        return [
            'type' => $type,
            'structure' => \App\Support\Grades\FormulaStructure::toPercentPayload(
                $this->resolveStructureConfigForKey($type)
            ),
        ];
    }

    protected function resolveStructureConfigForKey(string $structureKey, ?array $definitions = null): array
    {
        $definitions ??= FormulaStructure::getAllStructureDefinitions();
        $definition = $definitions[$structureKey] ?? null;

        $structureConfig = $definition['structure_config'] ?? null;

        if (is_string($structureConfig) && $structureConfig !== '') {
            $decoded = json_decode($structureConfig, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $structureConfig = $decoded;
            }
        }

        if (is_array($structureConfig) && ! empty($structureConfig)) {
            if ($this->isNewTemplateFormat($structureConfig)) {
                $structureConfig = $this->convertNewFormatToOld($structureConfig);
            }

            try {
                return FormulaStructure::normalize($structureConfig);
            } catch (\Throwable $exception) {
                // Fall through to the default structure when normalization fails.
            }
        }

        return FormulaStructure::normalize(FormulaStructure::default($structureKey));
    }

    protected function getStructureCatalog(): array
    {
        return collect(FormulaStructure::getAllStructureDefinitions())
            ->mapWithKeys(function ($meta, $key) {
                // Handle custom templates differently
                if (isset($meta['is_custom']) && $meta['is_custom']) {
                    $structureConfig = $meta['structure_config'] ?? [];

                    if (is_string($structureConfig)) {
                        $decoded = json_decode($structureConfig, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $structureConfig = $decoded;
                        } else {
                            $structureConfig = [];
                        }
                    }

                    if (! is_array($structureConfig) || empty($structureConfig)) {
                        $structureConfig = FormulaStructure::default('lecture_only');
                    }

                    // Check if this is the NEW format (with is_main, parent_id)
                    // and convert it to the OLD format (with key, type, children)
                    if ($this->isNewTemplateFormat($structureConfig)) {
                        $structureConfig = $this->convertNewFormatToOld($structureConfig);
                    }

                    try {
                        $structurePayload = FormulaStructure::toPercentPayload($structureConfig);
                    } catch (\Throwable $exception) {
                        $structurePayload = FormulaStructure::toPercentPayload(FormulaStructure::default('lecture_only'));
                    }

                    return [
                        $key => [
                            'id' => $meta['id'] ?? null,
                            'template_key' => $meta['template_key'] ?? $key,
                            'label' => $meta['label'],
                            'description' => $meta['description'],
                            'structure' => $structurePayload,
                            'is_custom' => true,
                            'is_system_default' => (bool) ($meta['is_system_default'] ?? false),
                        ],
                    ];
                }

                // Handle hardcoded templates
                return [
                    $key => [
                        'id' => null,
                        'template_key' => $key,
                        'label' => $meta['label'],
                        'description' => $meta['description'],
                        'structure' => FormulaStructure::toPercentPayload(FormulaStructure::default($key)),
                        'is_custom' => false,
                        'is_system_default' => true,
                    ],
                ];
            })
            ->toArray();
    }

    protected function isNewTemplateFormat(array $config): bool
    {
        // New format has a top-level 'type' and 'structure' key
        // with 'structure' being an array of components with 'is_main' flags
        if (! isset($config['structure']) || ! is_array($config['structure'])) {
            return false;
        }

        // Check if any entry has 'is_main' key (new format indicator)
        foreach ($config['structure'] as $entry) {
            if (is_array($entry) && array_key_exists('is_main', $entry)) {
                return true;
            }
        }

        return false;
    }

    protected function convertNewFormatToOld(array $config): array
    {
        $entries = $config['structure'] ?? [];

        // Separate main and sub components
        $mainComponents = [];
        $subComponents = [];
        $mainCounter = 1;

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $isMain = (bool) ($entry['is_main'] ?? false);

            if ($isMain) {
                // Use component_id if available, otherwise generate from counter
                $componentId = $entry['component_id'] ?? 'comp_'.$mainCounter;
                $mainComponents[] = [
                    'entry' => $entry,
                    'id' => $componentId,
                    'children' => [],
                ];
                $mainCounter++;
            } else {
                $subComponents[] = $entry;
            }
        }

        // Build lookup map for main components
        $componentLookup = [];
        foreach ($mainComponents as $index => $component) {
            if ($component['id'] !== null) {
                $componentLookup[$component['id']] = $index;
            }
        }

        // Attach sub-components to their parents
        foreach ($subComponents as $sub) {
            $parentId = $sub['parent_id'] ?? null;

            if ($parentId !== null && isset($componentLookup[$parentId])) {
                $parentIndex = $componentLookup[$parentId];
                $mainComponents[$parentIndex]['children'][] = $sub;
            }
        }

        // Build the old format structure
        $children = [];

        foreach ($mainComponents as $component) {
            $entry = $component['entry'];
            $subs = $component['children'];

            $activityType = $entry['activity_type'] ?? 'component';
            $label = $entry['label'] ?? 'Component';
            $weight = isset($entry['weight']) ? (float) $entry['weight'] / 100.0 : 0.0;
            $maxAssessments = $this->normalizeMaxAssessments($entry['max_items'] ?? null);

            $key = Str::slug($activityType, '_');

            if (empty($subs)) {
                // No sub-components, create a simple activity node
                $children[] = [
                    'key' => $key,
                    'type' => 'activity',
                    'label' => $label,
                    'activity_type' => $activityType,
                    'weight' => $weight,
                    'max_assessments' => $maxAssessments,
                ];
            } else {
                // Has sub-components, create a composite node
                $subChildren = [];

                foreach ($subs as $sub) {
                    $subActivityType = $sub['activity_type'] ?? 'component';
                    $subLabel = $sub['label'] ?? 'Component';
                    $subWeight = isset($sub['weight']) ? (float) $sub['weight'] / 100.0 : 0.0;
                    $subKey = Str::slug($key.'.'.$subActivityType, '_');
                    $subMaxAssessments = $this->normalizeMaxAssessments($sub['max_items'] ?? null);

                    $subChildren[] = [
                        'key' => $subKey,
                        'type' => 'activity',
                        'label' => $subLabel,
                        'activity_type' => $key.'.'.$subActivityType,
                        'weight' => $subWeight,
                        'max_assessments' => $subMaxAssessments,
                    ];
                }

                $children[] = [
                    'key' => $key,
                    'type' => 'composite',
                    'label' => $label,
                    'weight' => $weight,
                    'children' => $subChildren,
                ];
            }
        }

        return [
            'key' => 'period_grade',
            'type' => 'composite',
            'label' => 'Period Grade',
            'children' => $children,
        ];
    }

    protected function normalizeMaxAssessments($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        if ($intValue < 1) {
            return null;
        }

        return min($intValue, 5);
    }

    protected function normalizeTemplateIdentifier(?string $value, string $fallback): string
    {
        $candidate = Str::slug((string) $value, '_');

        if ($candidate === '') {
            $candidate = Str::slug($fallback, '_');
        }

        return $candidate !== '' ? $candidate : 'component_'.Str::random(6);
    }

    protected function buildStructureWeightDisplay(array $structure): array
    {
        $weights = [];
        $children = $structure['children'] ?? [];

        foreach ($children as $child) {
            $childType = $child['type'] ?? 'activity';
            $childWeight = isset($child['weight']) ? (float) $child['weight'] : 0.0;
            $childLabel = $child['label'] ?? FormulaStructure::formatLabel($child['key'] ?? 'component');
            $childPercent = (int) round($childWeight * 100);
            $maxAssessments = $child['max_assessments'] ?? null;

            if ($childType === 'composite' && ! empty($child['children'])) {
                // This is a composite node (e.g., "Lecture Component 60%")
                // Add the main component weight
                $weights[] = [
                    'type' => $childLabel,
                    'percent' => $childPercent,
                    'is_composite' => true,
                    'max_items' => null, // Composites don't have max items
                ];

                // Add sub-components with relative weights
                foreach ($child['children'] as $subChild) {
                    $subWeight = isset($subChild['weight']) ? (float) $subChild['weight'] : 0.0;
                    $subActivityType = $subChild['activity_type'] ?? $subChild['key'] ?? 'component';
                    $subLabel = $subChild['label'] ?? FormulaStructure::formatLabel($subActivityType);
                    $subPercent = (int) round($subWeight * 100);
                    $subMaxAssessments = $subChild['max_assessments'] ?? null;

                    $weights[] = [
                        'type' => $subLabel,
                        'percent' => $subPercent,
                        'is_sub' => true,
                        'parent_label' => $childLabel,
                        'max_items' => $subMaxAssessments,
                    ];
                }
            } else {
                // This is a simple activity node
                $activityType = $child['activity_type'] ?? $child['key'] ?? 'component';
                $label = $childLabel;

                $weights[] = [
                    'type' => $label,
                    'percent' => $childPercent,
                    'is_composite' => false,
                    'max_items' => $maxAssessments,
                ];
            }
        }

        return $weights;
    }

    protected function buildStructureTemplateConfig(array $components): array
    {
        if (empty($components)) {
            throw ValidationException::withMessages([
                'components' => 'Add at least one main component before saving the template.',
            ]);
        }

        $mainComponents = [];
        $subComponents = [];

        foreach ($components as $id => $component) {
            $component = is_array($component) ? $component : [];
            $activityType = trim((string) ($component['activity_type'] ?? ''));
            $label = trim((string) ($component['label'] ?? ''));
            $weight = (float) ($component['weight'] ?? 0);
            $maxItems = isset($component['max_items']) && $component['max_items'] !== '' ? (int) $component['max_items'] : null;
            $isMain = ! empty($component['is_main']);
            $parentId = $component['parent_id'] ?? null;

            $normalized = [
                'activity_type' => $activityType,
                'label' => $label,
                'weight' => $weight,
                'max_items' => $maxItems,
                'is_main' => $isMain,
                'parent_id' => $parentId,
            ];

            if ($isMain) {
                $mainComponents[$id] = $normalized;
            } elseif ($parentId !== null && $parentId !== '') {
                $subComponents[$parentId] ??= [];
                $subComponents[$parentId][] = $normalized;
            }
        }

        if (empty($mainComponents)) {
            throw ValidationException::withMessages([
                'components' => 'Add at least one main component before saving the template.',
            ]);
        }

        foreach ($subComponents as $parentId => $subs) {
            if (! isset($mainComponents[$parentId])) {
                throw ValidationException::withMessages([
                    'components' => 'All sub-components must belong to a valid main component.',
                ]);
            }
        }

        $totalMainWeight = array_sum(array_column($mainComponents, 'weight'));
        if (abs($totalMainWeight - 100) > 0.1) {
            throw ValidationException::withMessages([
                'components' => "Total weight of main components must equal 100%. Current total: {$totalMainWeight}%",
            ]);
        }

        foreach ($subComponents as $parentId => $subs) {
            $subTotal = array_sum(array_column($subs, 'weight'));
            if (abs($subTotal - 100) > 0.1) {
                $parentLabel = $mainComponents[$parentId]['label'] ?? "Component {$parentId}";
                throw ValidationException::withMessages([
                    'components' => "Sub-components of '{$parentLabel}' must total 100%. Current: {$subTotal}%",
                ]);
            }
        }

        foreach ($mainComponents as $id => &$mainComponent) {
            $identifierSource = $mainComponent['activity_type'] ?: $mainComponent['label'];
            $mainComponent['normalized_identifier'] = $this->normalizeTemplateIdentifier($identifierSource, 'component_'.$id);
        }
        unset($mainComponent);

        foreach ($subComponents as $parentId => &$subs) {
            foreach ($subs as $index => &$subComponent) {
                $identifierSource = $subComponent['activity_type'] ?: $subComponent['label'];
                $subComponent['normalized_identifier'] = $this->normalizeTemplateIdentifier(
                    $identifierSource,
                    'component_'.$parentId.'_child_'.($index + 1)
                );
            }
            unset($subComponent);
        }
        unset($subs);

        $structureConfig = [
            'key' => 'period_grade',
            'type' => 'composite',
            'label' => 'Period Grade',
            'children' => [],
        ];

        foreach ($mainComponents as $id => $mainComponent) {
            $mainWeight = $mainComponent['weight'] / 100;
            $mainIdentifier = $mainComponent['normalized_identifier'];
            $label = $mainComponent['label'];
            $maxItems = $mainComponent['max_items'];

            if (isset($subComponents[$id]) && count($subComponents[$id]) > 0) {
                $children = [];

                foreach ($subComponents[$id] as $subComponent) {
                    $childIdentifier = $subComponent['normalized_identifier'];
                    $compositeActivityType = $mainIdentifier.'.'.$childIdentifier;

                    $childNode = [
                        'key' => $compositeActivityType,
                        'type' => 'activity',
                        'label' => $subComponent['label'],
                        'activity_type' => $compositeActivityType,
                        'weight' => $subComponent['weight'] / 100,
                    ];

                    if ($subComponent['max_items'] !== null) {
                        $childNode['max_assessments'] = $subComponent['max_items'];
                    }

                    $children[] = $childNode;
                }

                $structureConfig['children'][] = [
                    'key' => $mainIdentifier,
                    'type' => 'composite',
                    'label' => $label,
                    'weight' => $mainWeight,
                    'children' => $children,
                ];
            } else {
                $mainNode = [
                    'key' => $mainIdentifier,
                    'type' => 'activity',
                    'label' => $label,
                    'activity_type' => $mainIdentifier,
                    'weight' => $mainWeight,
                ];

                if ($maxItems !== null) {
                    $mainNode['max_assessments'] = $maxItems;
                }

                $structureConfig['children'][] = $mainNode;
            }
        }

        return $structureConfig;
    }
}
