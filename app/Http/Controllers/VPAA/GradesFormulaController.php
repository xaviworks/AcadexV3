<?php

namespace App\Http\Controllers\VPAA;

use App\Http\Controllers\Controller;
use App\Http\Controllers\VPAA\Concerns\ManagesGradingConfiguration;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Department;
use App\Models\FinalGrade;
use App\Models\GradesFormula;
use App\Models\Subject;
use App\Models\TermGrade;
use App\Services\GradesFormulaService;
use App\Support\Grades\FormulaStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GradesFormulaController extends Controller
{
    use ManagesGradingConfiguration;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('manage-grading-configuration');

        $request = request();

        // Check if academic period has been selected
        $hasAcademicPeriodSelected = $request->filled('academic_period_id');

        // If no academic period selected, show the period selection page
        if (! $hasAcademicPeriodSelected) {
            $academicPeriods = AcademicPeriod::orderBy('academic_year', 'desc')
                ->orderBy('semester')
                ->get();

            return view('vpaa.grading-configuration.grades-formula-select-period', [
                'academicPeriods' => $academicPeriods,
            ]);
        }

        // Academic period selected, proceed with the main grades formula page
        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'] ?? null;
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $departments = Department::where('is_deleted', false)
            ->with(['courses' => function ($query) use ($selectedAcademicPeriodId) {
                $query->where('is_deleted', false)
                    ->with(['subjects' => function ($subjectQuery) use ($selectedAcademicPeriodId) {
                        $subjectQuery->where('is_deleted', false)
                            ->when($selectedAcademicPeriodId, fn ($q, $periodId) => $q->where('academic_period_id', $periodId))
                            ->select('id', 'course_id', 'subject_code', 'subject_description', 'academic_period_id');
                    }])
                    ->select('id', 'department_id', 'course_code', 'course_description', 'is_deleted');
            }])
            ->orderBy('department_code')
            ->get();

        $subjectIds = $departments
            ->flatMap(fn (Department $department) => ($department->courses ?? collect())->flatMap(fn (Course $course) => $course->subjects ?? collect()))
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $subjectsWithGrades = collect();

        if ($subjectIds->isNotEmpty()) {
            $termGradeSubjects = TermGrade::whereIn('subject_id', $subjectIds)
                ->where('is_deleted', false)
                ->when($selectedAcademicPeriodId, fn ($query, $periodId) => $query->where('academic_period_id', $periodId))
                ->pluck('subject_id');

            $finalGradeSubjects = FinalGrade::whereIn('subject_id', $subjectIds)
                ->where('is_deleted', false)
                ->when($selectedAcademicPeriodId, fn ($query, $periodId) => $query->where('academic_period_id', $periodId))
                ->pluck('subject_id');

            $activitySubjects = Activity::whereIn('subject_id', $subjectIds)
                ->where('is_deleted', false)
                ->whereHas('scores', fn ($query) => $query->where('is_deleted', false))
                ->pluck('subject_id');

            $subjectsWithGrades = collect()
                ->merge($termGradeSubjects)
                ->merge($finalGradeSubjects)
                ->merge($activitySubjects)
                ->unique()
                ->values();
        }

        $subjectsWithGradesMap = $subjectsWithGrades->flip();

        $departments = $departments->map(function (Department $department) use ($subjectsWithGradesMap) {
            $courses = $department->courses ?? collect();

            $department->setRelation('courses', $courses->map(function (Course $course) use ($subjectsWithGradesMap) {
                $subjects = $course->subjects ?? collect();

                $course->setRelation('subjects', $subjects->map(function (Subject $subject) use ($subjectsWithGradesMap) {
                    $subject->setAttribute('has_recorded_grades', $subjectsWithGradesMap->has($subject->id));

                    return $subject;
                })->values());

                return $course;
            })->values());

            return $department;
        });

        $departmentIds = $departments->pluck('id');

        $fallbacks = $this->applyPeriodFilters(
            GradesFormula::whereIn('department_id', $departmentIds)
                ->where('scope_level', 'department')
                ->where('is_department_fallback', true),
            $selectedSemester,
            $selectedAcademicPeriodId
        )
            ->get()
            ->keyBy('department_id');

        $missingFallbacks = $departmentIds->diff($fallbacks->keys());

        foreach ($missingFallbacks as $departmentId) {
            $department = $departments->firstWhere('id', $departmentId);
            if ($department) {
                $fallbacks->put($departmentId, $this->ensureDepartmentFallback($department, $periodContext));
            }
        }

        $departmentCatalogs = GradesFormula::with('weights')
            ->whereIn('department_id', $departmentIds)
            ->where('scope_level', 'department')
            ->get()
            ->groupBy('department_id');

        $courseFormulas = $this->applyPeriodFilters(
            GradesFormula::whereNotNull('course_id')
                ->where('scope_level', 'course'),
            $selectedSemester,
            $selectedAcademicPeriodId
        )
            ->get(['id', 'course_id', 'label'])
            ->keyBy('course_id');

        $subjectFormulaQuery = GradesFormula::where('scope_level', 'subject')
            ->whereNotNull('subject_id');

        if ($subjectIds->isNotEmpty()) {
            $subjectFormulaQuery->whereIn('subject_id', $subjectIds);
        }

        $subjectFormulas = $this->applyPeriodFilters(
            $subjectFormulaQuery,
            $selectedSemester,
            $selectedAcademicPeriodId
        )
            ->get(['id', 'subject_id', 'label'])
            ->keyBy('subject_id');

        $globalFormula = $this->getGlobalFormula();

        $departmentsSummary = $departments->map(function (Department $department) use (
            $fallbacks,
            $departmentCatalogs,
            $courseFormulas,
            $subjectFormulas,
            $globalFormula,
            $selectedSemester,
            $selectedAcademicPeriodId
        ) {
            $courses = $department->courses;

            $courseCount = $courses->count();
            $coursesWithFormula = $courses->filter(fn ($course) => $courseFormulas->has($course->id))->count();

            $subjects = $courses->flatMap(fn (Course $course) => $course->subjects ?? collect());
            $subjectCount = $subjects->count();
            $subjectsWithFormula = $subjects->filter(fn ($subject) => $subjectFormulas->has($subject->id))->count();

            $fallback = $fallbacks->get($department->id) ?? $globalFormula;
            $catalog = $departmentCatalogs->get($department->id, collect());
            $nonFallbackCount = $catalog->filter(fn ($formula) => ! $formula->is_department_fallback)->count();
            $matchingCatalogCount = $catalog->filter(function ($formula) use ($selectedSemester, $selectedAcademicPeriodId) {
                if ($formula->is_department_fallback) {
                    return false;
                }

                $semesterMatches = $selectedSemester === null
                    ? $formula->semester === null
                    : $formula->semester === $selectedSemester;

                $periodMatches = $selectedAcademicPeriodId === null
                    ? $formula->academic_period_id === null
                    : (int) $formula->academic_period_id === (int) $selectedAcademicPeriodId;

                return $semesterMatches && $periodMatches;
            })->count();

            if ($matchingCatalogCount > 0) {
                $status = 'custom';
                $scopeText = 'Catalog ready with formulas for this period.';
            } elseif ($nonFallbackCount > 0) {
                $status = 'custom';
                $scopeText = 'Catalog formulas available in other periods.';
            } else {
                $status = 'default';
                $scopeText = 'Using baseline department formula.';
            }

            return [
                'department' => $department,
                'catalog_count' => $nonFallbackCount,
                'catalog_available_count' => $matchingCatalogCount,
                'missing_course_count' => max($courseCount - $coursesWithFormula, 0),
                'missing_subject_count' => max($subjectCount - $subjectsWithFormula, 0),
                'formula_label' => $fallback->label ?? $globalFormula->label,
                'formula_scope' => 'Department Baseline',
                'status' => $status,
                'scope_text' => $scopeText,
            ];
        });

        $structureCatalog = collect($this->getStructureCatalog())
            ->map(function (array $entry, string $key) {
                $normalized = FormulaStructure::fromPercentPayload($entry['structure'] ?? []);

                // Build a better weight display that shows hierarchical structure
                $weights = $this->buildStructureWeightDisplay($normalized);

                return [
                    'id' => $entry['id'] ?? null,
                    'template_key' => $entry['template_key'] ?? $key,
                    'key' => $key,
                    'label' => $entry['label'] ?? FormulaStructure::formatLabel($key),
                    'description' => $entry['description'] ?? '',
                    'weights' => $weights,
                    'structure' => $entry['structure'] ?? [],
                    'is_custom' => (bool) ($entry['is_custom'] ?? false),
                    'is_system_default' => (bool) ($entry['is_system_default'] ?? false),
                ];
            })
            ->values();

        // Fetch global formulas
        $globalFormulasList = GradesFormula::with('weights')
            ->where('scope_level', 'global')
            ->whereNull('department_id')
            ->whereNull('course_id')
            ->whereNull('subject_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('vpaa.grading-configuration.grades-formula-wildcards', [
            'globalFormula' => $globalFormula,
            'globalFormulasList' => $globalFormulasList,
            'departmentsSummary' => $departmentsSummary,
            'departments' => $departments,
            'departmentFallbacks' => $fallbacks,
            'departmentCatalogs' => $departmentCatalogs,
            'courseFormulas' => $courseFormulas,
            'subjectFormulas' => $subjectFormulas,
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
            'structureCatalog' => $structureCatalog,
        ]);
    }

    public function applyDepartmentTemplate(Request $request, Department $department)
    {
        Gate::authorize('manage-grading-configuration');

        if ($department->is_deleted) {
            abort(404);
        }

        $structureDefinitions = FormulaStructure::getAllStructureDefinitions();
        $templateKeys = array_keys($structureDefinitions);

        $validated = $request->validate([
            'template_key' => ['required', 'string', Rule::in($templateKeys)],
        ]);

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $academicPeriods = $periodContext['academic_periods'];

        $structureKey = $validated['template_key'];
        $structure = $this->resolveStructureConfigForKey($structureKey, $structureDefinitions);
        $structureErrors = FormulaStructure::validate($structure);

        if (! empty($structureErrors)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => implode(' ', $structureErrors),
                ], 422);
            }

            return back()
                ->withErrors(['template_key' => implode(' ', $structureErrors)])
                ->withInput();
        }

        $weightInserts = collect(FormulaStructure::flattenWeights($structure))
            ->map(fn (array $node) => [
                'activity_type' => $node['activity_type'],
                'weight' => $node['weight'],
            ])
            ->values();

        $fallback = $this->ensureDepartmentFallback($department, $periodContext);
        $fallback->loadMissing('weights');

        DB::transaction(function () use ($fallback, $structureKey, $structure, $weightInserts) {
            $fallback->structure_type = $structureKey;
            $fallback->structure_config = $structure;
            $fallback->save();

            $fallback->weights()->delete();

            if ($weightInserts->isNotEmpty()) {
                $fallback->weights()->createMany($weightInserts->all());
            }
        });

        GradesFormulaService::flushCache();

        $fallback = $fallback->fresh('weights');

        $periodLookup = collect($academicPeriods ?? [])->keyBy('id');

        $contextParts = [];
        if ($fallback->academic_period_id) {
            $period = $periodLookup->get($fallback->academic_period_id);
            if ($period) {
                $contextParts[] = trim($period->academic_year ?? '') !== ''
                    ? $period->academic_year
                    : 'Academic Period #'.$fallback->academic_period_id;
                if (! empty($period->semester)) {
                    $contextParts[] = trim($period->semester).' Semester';
                }
            } else {
                $contextParts[] = 'Academic Period #'.$fallback->academic_period_id;
            }
        }

        if ($fallback->academic_period_id === null && $fallback->semester) {
            $contextParts[] = trim($fallback->semester).' Semester';
        }

        if (empty($contextParts)) {
            $contextParts[] = 'Applies to all periods';
        }

        $contextLabel = implode(' · ', array_filter($contextParts));

        $weightDisplay = collect($fallback->weight_map)
            ->map(fn ($weight, $type) => [
                'type' => strtoupper($type),
                'percent' => number_format($weight * 100, 0),
            ])
            ->values()
            ->all();

        $structureDefinitions = FormulaStructure::getAllStructureDefinitions();
        $structureLabel = $structureDefinitions[$fallback->structure_type]['label']
            ?? FormulaStructure::formatLabel($fallback->structure_type ?? 'template');

        $payload = [
            'id' => $fallback->id,
            'label' => $fallback->label,
            'base_score' => $fallback->base_score,
            'scale_multiplier' => $fallback->scale_multiplier,
            'passing_grade' => $fallback->passing_grade,
            'is_fallback' => true,
            'context_match' => $this->formulaMatchesContext($fallback, $selectedSemester, $selectedAcademicPeriodId),
            'context_label' => $contextLabel,
            'semester' => $fallback->semester,
            'academic_period_id' => $fallback->academic_period_id,
            'weights' => $weightDisplay,
            'edit_url' => route('vpaa.gradingConfiguration.departments.edit', array_merge([
                'department' => $department->id,
            ], $this->formulaQueryParams())),
            'updated_at' => optional($fallback->updated_at)->diffForHumans() ?? 'Just now',
            'structure_type' => $fallback->structure_type,
            'structure_label' => $structureLabel,
        ];

        $departmentLabel = trim($department->department_description ?? 'Department');
        $message = sprintf(
            'Applied the %s template to %s\'s baseline formula.',
            $structureLabel,
            $departmentLabel !== '' ? $departmentLabel : 'the department'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'formula' => $payload,
            ]);
        }

        return redirect()
            ->route('vpaa.gradingConfiguration.index', $this->formulaQueryParams())
            ->with('success', $message);
    }

    public function showDefault()
    {
        Gate::authorize('manage-grading-configuration');

        $defaultFormula = $this->getGlobalFormula();

        $structurePayload = $this->prepareStructurePayload($defaultFormula);

        return view('vpaa.grading-configuration.grades-formula-form', [
            'context' => 'default',
            'department' => null,
            'course' => null,
            'subject' => null,
            'formula' => $defaultFormula,
            'fallbackFormula' => $defaultFormula,
            'structurePayload' => $structurePayload,
            'structureCatalog' => $this->getStructureCatalog(),
            'defaultFormula' => $defaultFormula,
        ]);
    }

    public function showDepartment(Department $department)
    {
        Gate::authorize('manage-grading-configuration');

        if ($department->is_deleted) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $department->load(['courses' => function ($query) use ($selectedAcademicPeriodId) {
            $query->where('is_deleted', false)
                ->withCount(['subjects' => function ($subjectQuery) use ($selectedAcademicPeriodId) {
                    $subjectQuery->where('is_deleted', false)
                        ->when($selectedAcademicPeriodId, fn ($q, $periodId) => $q->where('academic_period_id', $periodId));
                }])
                ->with(['subjects' => function ($subjectQuery) use ($selectedAcademicPeriodId) {
                    $subjectQuery->where('is_deleted', false)
                        ->when($selectedAcademicPeriodId, fn ($q, $periodId) => $q->where('academic_period_id', $periodId))
                        ->select('id', 'course_id', 'subject_code', 'subject_description', 'academic_period_id');
                }])
                ->orderBy('course_code');
        }]);

        $fallbackFormula = $this->ensureDepartmentFallback($department, $periodContext);
        $fallbackFormula->loadMissing('weights');

        $globalFormula = $this->getGlobalFormula();

        $courseFormulas = $this->applyPeriodFilters(
            GradesFormula::whereIn('course_id', $department->courses->pluck('id'))
                ->where('scope_level', 'course'),
            $selectedSemester,
            $selectedAcademicPeriodId
        )
            ->get(['id', 'course_id', 'label'])
            ->keyBy('course_id');

        $subjectIds = $department->courses->flatMap(fn (Course $course) => $course->subjects ?? collect())->pluck('id');
        $subjectFormulaIds = $this->applyPeriodFilters(
            GradesFormula::whereIn('subject_id', $subjectIds)
                ->where('scope_level', 'subject'),
            $selectedSemester,
            $selectedAcademicPeriodId
        )
            ->pluck('subject_id')
            ->toArray();

        $courseSummaries = $department->courses->map(function (Course $course) use ($courseFormulas, $subjectFormulaIds, $fallbackFormula, $globalFormula) {
            $subjects = $course->subjects ?? collect();
            $subjectIds = $subjects->pluck('id');
            $subjectCount = $course->subjects_count ?? $subjectIds->count();
            $subjectsWithFormula = $subjectIds->filter(fn ($subjectId) => in_array($subjectId, $subjectFormulaIds))->count();

            $courseFormula = $courseFormulas->get($course->id);
            $hasCourseFormula = (bool) $courseFormula;

            if ($hasCourseFormula) {
                $formulaScope = 'Course Formula';
                $formulaLabel = $courseFormula->label;
                $status = 'custom';
                $scopeText = 'Course formula applied.';
            } elseif ($fallbackFormula) {
                $formulaScope = 'Department Baseline';
                $formulaLabel = $fallbackFormula->label;
                $status = 'default';
                $scopeText = 'Using department baseline formula.';
            } else {
                $formulaScope = 'Institution Fallback Formula';
                $formulaLabel = $globalFormula->label;
                $status = 'default';
                $scopeText = 'Using institution fallback formula.';
            }

            return [
                'course' => $course,
                'has_formula' => $hasCourseFormula,
                'missing_subject_count' => max($subjectCount - $subjectsWithFormula, 0),
                'formula_label' => $formulaLabel,
                'formula_scope' => $formulaScope,
                'status' => $status,
                'scope_text' => $scopeText,
            ];
        });

        return view('vpaa.grading-configuration.grades-formula-department', [
            'department' => $department,
            'departmentFallback' => $fallbackFormula,
            'globalFormula' => $globalFormula,
            'courseSummaries' => $courseSummaries,
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
        ]);
    }

    public function showCourse(Department $department, Course $course)
    {
        Gate::authorize('manage-grading-configuration');

        if ($department->is_deleted || $course->is_deleted || $course->department_id !== $department->id) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $course->load(['subjects' => function ($query) use ($selectedAcademicPeriodId) {
            $query->where('is_deleted', false)
                ->when($selectedAcademicPeriodId, fn ($q, $periodId) => $q->where('academic_period_id', $periodId))
                ->orderBy('subject_code')
                ->select('id', 'course_id', 'subject_code', 'subject_description', 'department_id', 'academic_period_id', 'is_deleted');
        }]);

        $departmentFallback = $this->ensureDepartmentFallback($department, $periodContext);
        $departmentFallback->loadMissing('weights');

        $courseFormulaQuery = GradesFormula::with('weights')
            ->where('course_id', $course->id)
            ->where('scope_level', 'course');

        $courseFormulaQuery = $this->applyPeriodFilters($courseFormulaQuery, $selectedSemester, $selectedAcademicPeriodId);

        if ($selectedAcademicPeriodId) {
            $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
        } else {
            $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
        }

        if ($selectedSemester) {
            $courseFormulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
        } else {
            $courseFormulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
        }

        $courseFormula = $courseFormulaQuery->first();

        $globalFormula = $this->getGlobalFormula();

        $subjects = $course->subjects ?? collect();

        $subjectSummaries = $subjects->map(function (Subject $subject) use ($selectedSemester, $selectedAcademicPeriodId, $globalFormula) {
            $settings = GradesFormulaService::getSettings(
                $subject->id,
                $subject->course_id,
                $subject->department_id,
                $selectedSemester,
                $selectedAcademicPeriodId
            );

            $meta = $settings['meta'] ?? [];
            $scope = $meta['scope'] ?? 'global';
            $label = $meta['label'] ?? ($globalFormula->label ?? 'Institution Fallback');

            switch ($scope) {
                case 'subject':
                    $status = 'custom';
                    $formulaScope = 'Subject Formula';
                    $scopeText = 'Subject formula applied.';
                    break;
                case 'course':
                    $status = 'default';
                    $formulaScope = 'Course Formula';
                    $scopeText = 'Inherits course formula.';
                    break;
                case 'department':
                    $status = 'default';
                    $formulaScope = 'Department Baseline';
                    $scopeText = 'Inherits department baseline.';
                    break;
                default:
                    $status = 'default';
                    $formulaScope = 'Institution Fallback Formula';
                    $scopeText = 'Using institution fallback formula.';
                    break;
            }

            return [
                'subject' => $subject,
                'has_formula' => $scope === 'subject',
                'status' => $status,
                'formula_scope' => $formulaScope,
                'formula_label' => $label,
                'scope_text' => $scopeText,
            ];
        });

        return view('vpaa.grading-configuration.grades-formula-course', [
            'department' => $department,
            'course' => $course,
            'departmentFallback' => $departmentFallback,
            'courseFormula' => $courseFormula,
            'globalFormula' => $globalFormula,
            'subjectSummaries' => $subjectSummaries,
            'needsCourseFormula' => ! $courseFormula,
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
        ]);
    }

    public function editDepartment(Request $request, Department $department)
    {
        Gate::authorize('manage-grading-configuration');

        if ($department->is_deleted) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $formula = $this->ensureDepartmentFallback($department, $periodContext);
        $formula->loadMissing('weights');

        $fallbackFormula = $formula;

        $structurePayload = $this->prepareStructurePayload($formula);

        if (Str::startsWith($request->old('form_context'), 'department') && $request->old('structure_config')) {
            $structurePayload = $this->prepareStructurePayloadFromOldInput(
                $request->old('structure_type'),
                $request->old('structure_config')
            );
        }

        return view('vpaa.grading-configuration.grades-formula-form', [
            'context' => 'department',
            'department' => $department,
            'course' => null,
            'subject' => null,
            'formula' => $formula,
            'fallbackFormula' => $fallbackFormula,
            'structurePayload' => $structurePayload,
            'structureCatalog' => $this->getStructureCatalog(),
            'defaultFormula' => $this->getGlobalFormula(),
            'formMode' => 'edit-department-fallback',
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
        ]);
    }

    public function editCourse(Request $request, Department $department, Course $course)
    {
        Gate::authorize('manage-grading-configuration');

        if ($department->is_deleted || $course->is_deleted || $course->department_id !== $department->id) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $formulaQuery = GradesFormula::with('weights')
            ->where('course_id', $course->id)
            ->where('scope_level', 'course');

        $formulaQuery = $this->applyPeriodFilters($formulaQuery, $selectedSemester, $selectedAcademicPeriodId);

        if ($selectedAcademicPeriodId) {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
        }

        if ($selectedSemester) {
            $formulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
        }

        $rawFormula = $formulaQuery->first();

        $departmentFallback = $this->ensureDepartmentFallback($department, $periodContext);
        $departmentFallback->loadMissing('weights');

        $courseFormula = null;
        $fallbackCandidates = collect();

        if ($rawFormula && $this->formulaMatchesContext($rawFormula, $selectedSemester, $selectedAcademicPeriodId)) {
            $courseFormula = $rawFormula;
        } elseif ($rawFormula) {
            $fallbackCandidates->push($rawFormula);
        }

        if ($departmentFallback) {
            $fallbackCandidates->push($departmentFallback);
        }

        $fallbackFormula = $courseFormula
            ?? $fallbackCandidates->first()
            ?? $this->getGlobalFormula();

        $structurePayload = $this->prepareStructurePayload($courseFormula ?? $fallbackFormula);

        if (Str::startsWith($request->old('form_context'), 'course') && $request->old('structure_config')) {
            $structurePayload = $this->prepareStructurePayloadFromOldInput(
                $request->old('structure_type'),
                $request->old('structure_config')
            );
        }

        return view('vpaa.grading-configuration.grades-formula-form', [
            'context' => 'course',
            'department' => $department,
            'course' => $course,
            'subject' => null,
            'formula' => $courseFormula,
            'fallbackFormula' => $fallbackFormula,
            'structurePayload' => $structurePayload,
            'structureCatalog' => $this->getStructureCatalog(),
            'defaultFormula' => $this->getGlobalFormula(),
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
        ]);
    }

    public function destroy(GradesFormula $formula, Request $request)
    {
        Gate::authorize('manage-grading-configuration');

        // Validate password first
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify the password matches the authenticated user
        if (! Hash::check($request->input('password'), Auth::user()->password)) {
            return back()
                ->withErrors(['password' => 'The provided password is incorrect.'])
                ->withInput();
        }

        if ($formula->scope_level !== 'global') {
            abort(404, 'This formula is not a global formula.');
        }

        DB::transaction(function () use ($formula) {
            $formula->weights()->delete();
            $formula->delete();
        });

        GradesFormulaService::flushCache();

        return redirect()
            ->route('vpaa.gradingConfiguration.index', array_merge($this->formulaQueryParams(), ['view' => 'formulas']))
            ->with('success', 'Institution fallback formula deleted successfully.');
    }

    public function edit(GradesFormula $formula)
    {
        Gate::authorize('manage-grading-configuration');

        if ($formula->scope_level !== 'global') {
            abort(404, 'This formula is not a global formula.');
        }

        $formula->loadMissing('weights');

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        return view('vpaa.grading-configuration.grades-formula-edit-global', compact(
            'formula',
            'selectedSemester',
            'selectedAcademicPeriodId',
            'selectedAcademicYear',
            'academicPeriods',
            'academicYears'
        ));
    }

    public function editSubject(Request $request, Subject $subject)
    {
        Gate::authorize('manage-grading-configuration');

        if ($subject->is_deleted) {
            abort(404);
        }

        $subject->load(['course.department']);

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $formulaQuery = GradesFormula::with('weights')
            ->where('subject_id', $subject->id);

        $formulaQuery = $this->applyPeriodFilters($formulaQuery, $selectedSemester, $selectedAcademicPeriodId);

        if ($selectedAcademicPeriodId) {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
        }

        if ($selectedSemester) {
            $formulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
        }

        $rawSubjectFormula = $formulaQuery->first();

        $exactCourseFormula = null;
        if ($subject->course) {
            $courseFormulaQuery = GradesFormula::with('weights')
                ->where('course_id', $subject->course_id)
                ->where('scope_level', 'course');

            $courseFormulaQuery = $this->applyPeriodFilters($courseFormulaQuery, $selectedSemester, $selectedAcademicPeriodId);

            if ($selectedAcademicPeriodId) {
                $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
            } else {
                $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
            }

            if ($selectedSemester) {
                $courseFormulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
            } else {
                $courseFormulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
            }

            $courseFormula = $courseFormulaQuery->first();
            if ($courseFormula && $this->formulaMatchesContext($courseFormula, $selectedSemester, $selectedAcademicPeriodId)) {
                $exactCourseFormula = $courseFormula;
            }
        }

        $departmentFallback = null;
        if ($subject->department) {
            $departmentFallback = $this->ensureDepartmentFallback($subject->department, $periodContext);
            $departmentFallback->loadMissing('weights');
        }

        $subjectFormula = null;
        $fallbackCandidates = collect();

        if ($rawSubjectFormula && $this->formulaMatchesContext($rawSubjectFormula, $selectedSemester, $selectedAcademicPeriodId)) {
            $subjectFormula = $rawSubjectFormula;
        } elseif ($rawSubjectFormula) {
            $fallbackCandidates->push($rawSubjectFormula);
        }

        if ($exactCourseFormula) {
            $fallbackCandidates->push($exactCourseFormula);
        } elseif (isset($courseFormula) && $courseFormula) {
            $fallbackCandidates->push($courseFormula);
        }

        if ($departmentFallback) {
            $fallbackCandidates->push($departmentFallback);
        }

        $fallbackFormula = $subjectFormula
            ?? $fallbackCandidates->first()
            ?? $this->getGlobalFormula();

        $structurePayload = $this->prepareStructurePayload($subjectFormula ?? $fallbackFormula);

        if (Str::startsWith($request->old('form_context'), 'subject') && $request->old('structure_config')) {
            $structurePayload = $this->prepareStructurePayloadFromOldInput(
                $request->old('structure_type'),
                $request->old('structure_config')
            );
        }

        $subjectHasExistingGrades = $this->subjectHasRecordedGrades($subject, $selectedAcademicPeriodId);

        return view('vpaa.grading-configuration.grades-formula-form', [
            'context' => 'subject',
            'department' => $subject->department,
            'course' => $subject->course,
            'subject' => $subject,
            'formula' => $subjectFormula,
            'fallbackFormula' => $fallbackFormula,
            'structurePayload' => $structurePayload,
            'structureCatalog' => $this->getStructureCatalog(),
            'defaultFormula' => $this->getGlobalFormula(),
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
            // Password NOT required for editing existing formulas, only for applying templates
            'requiresPasswordConfirmation' => false,
        ]);
    }

    public function showSubject(Subject $subject)
    {
        Gate::authorize('manage-grading-configuration');

        if ($subject->is_deleted) {
            abort(404);
        }

        $subject->load(['course.department']);

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $selectedAcademicYear = $periodContext['academic_year'];
        $academicPeriods = $periodContext['academic_periods'];
        $academicYears = $periodContext['academic_years'];

        $formulaQuery = GradesFormula::with('weights')
            ->where('subject_id', $subject->id);

        $formulaQuery = $this->applyPeriodFilters($formulaQuery, $selectedSemester, $selectedAcademicPeriodId);

        if ($selectedAcademicPeriodId) {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
        }

        if ($selectedSemester) {
            $formulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
        } else {
            $formulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
        }

        $rawSubjectFormula = $formulaQuery->first();

        $courseFormula = null;
        $exactCourseFormula = null;
        if ($subject->course) {
            $courseFormulaQuery = GradesFormula::with('weights')
                ->where('course_id', $subject->course_id)
                ->where('scope_level', 'course');

            $courseFormulaQuery = $this->applyPeriodFilters($courseFormulaQuery, $selectedSemester, $selectedAcademicPeriodId);

            if ($selectedAcademicPeriodId) {
                $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
            } else {
                $courseFormulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
            }

            if ($selectedSemester) {
                $courseFormulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
            } else {
                $courseFormulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
            }

            $courseFormula = $courseFormulaQuery->first();
            if ($courseFormula && $this->formulaMatchesContext($courseFormula, $selectedSemester, $selectedAcademicPeriodId)) {
                $exactCourseFormula = $courseFormula;
            }
        }

        $departmentFallback = null;

        if ($subject->department) {
            $departmentFallback = $this->ensureDepartmentFallback($subject->department, $periodContext);
            $departmentFallback->loadMissing('weights');
        }

        $globalFormula = $this->getGlobalFormula();

        $subjectFormula = null;
        $subjectFallbackCandidates = collect();

        if ($rawSubjectFormula && $this->formulaMatchesContext($rawSubjectFormula, $selectedSemester, $selectedAcademicPeriodId)) {
            $subjectFormula = $rawSubjectFormula;
        } elseif ($rawSubjectFormula) {
            $subjectFallbackCandidates->push($rawSubjectFormula);
        }

        if ($exactCourseFormula) {
            $subjectFallbackCandidates->push($exactCourseFormula);
        } elseif ($courseFormula) {
            $subjectFallbackCandidates->push($courseFormula);
        }

        if ($departmentFallback) {
            $subjectFallbackCandidates->push($departmentFallback);
        }

        $activeScope = $subjectFormula
            ? 'subject'
            : ($exactCourseFormula
                ? 'course'
                : ($departmentFallback ? 'department' : 'default'));

        $resolvedSettings = GradesFormulaService::getSettings(
            $subject->id,
            $subject->course_id,
            $subject->department_id,
            $selectedSemester,
            $selectedAcademicPeriodId
        );

        $courseFormulaForView = $exactCourseFormula ?? $courseFormula;
        $subjectFallback = $subjectFallbackCandidates->first() ?? $globalFormula;

        $allStructureDefinitions = FormulaStructure::getAllStructureDefinitions();
        $structureDefinitions = collect($allStructureDefinitions);

        $structureOptions = $structureDefinitions
            ->map(function (array $definition, string $key) {
                return [
                    'key' => $key,
                    'label' => $definition['label'] ?? Str::of($key)->replace('_', ' ')->title()->toString(),
                ];
            })
            ->values();

        $baselineFormula = $departmentFallback ?? $globalFormula;
        $baselineStructureType = $baselineFormula->structure_type ?? 'lecture_only';

        $structureBlueprints = $structureDefinitions
            ->map(function (array $definition, string $key) use ($baselineFormula, $baselineStructureType, $allStructureDefinitions) {
                $structure = $this->resolveStructureConfigForKey($key, $allStructureDefinitions);
                $flattened = collect(FormulaStructure::flattenWeights($structure));

                $weights = $flattened
                    ->map(function (array $entry) {
                        $activityType = $entry['activity_type'] ?? $entry['key'] ?? 'component';
                        $weight = (float) ($entry['weight'] ?? 0);
                        $formattedLabel = Str::of($entry['label'] ?? FormulaStructure::formatLabel($activityType))
                            ->replace(['.', '_'], ' ')
                            ->upper()
                            ->toString();

                        return [
                            'type' => $formattedLabel,
                            'label' => $formattedLabel,
                            'display' => number_format($weight * 100, 0),
                            'progress' => $weight,
                        ];
                    })
                    ->values();

                return [
                    'key' => $key,
                    'label' => $definition['label'] ?? Str::of($key)->replace('_', ' ')->title()->toString(),
                    'description' => $definition['description'] ?? null,
                    'base_score' => (float) ($baselineFormula->base_score ?? 40),
                    'scale_multiplier' => (float) ($baselineFormula->scale_multiplier ?? 60),
                    'passing_grade' => (float) ($baselineFormula->passing_grade ?? 75),
                    'weights' => $weights,
                    'is_baseline' => $baselineStructureType === $key,
                ];
            })
            ->values();

        $activeStructureType = $resolvedSettings['meta']['structure_type']
            ?? ($subjectFormula->structure_type ?? null)
            ?? $baselineStructureType;

        $subjectHasExistingGrades = $this->subjectHasRecordedGrades($subject, $selectedAcademicPeriodId);

        return view('vpaa.grading-configuration.grades-formula-subject', [
            'subject' => $subject,
            'course' => $subject->course,
            'department' => $subject->department,
            'subjectFormula' => $subjectFormula,
            'courseFormula' => $courseFormulaForView,
            'departmentFallback' => $departmentFallback,
            'globalFormula' => $globalFormula,
            'activeScope' => $activeScope,
            'activeMeta' => $resolvedSettings['meta'] ?? [],
            'semester' => $selectedSemester,
            'academicPeriods' => $academicPeriods,
            'academicYears' => $academicYears,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedAcademicPeriodId' => $selectedAcademicPeriodId,
            'availableSemesters' => $periodContext['available_semesters'],
            'fallbackFormula' => $subjectFallback,
            'structureOptions' => $structureOptions,
            'structureBlueprints' => $structureBlueprints,
            'selectedStructureType' => $activeStructureType,
            'requiresPasswordConfirmation' => $subjectHasExistingGrades,
        ]);
    }

    public function applySubjectFormula(Request $request, Subject $subject)
    {
        Gate::authorize('manage-grading-configuration');

        if ($subject->is_deleted) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];

        $subjectRequiresPassword = $this->subjectHasRecordedGrades($subject, $selectedAcademicPeriodId);

        $validated = $request->validate([
            'department_formula_id' => ['nullable', 'integer'],
            'structure_type' => ['nullable', Rule::in(array_keys(FormulaStructure::getAllStructureDefinitions()))],
            'current_password' => $subjectRequiresPassword ? ['required', 'current_password'] : ['nullable'],
        ]);

        if (empty($validated['department_formula_id']) && empty($validated['structure_type'])) {
            return back()
                ->withErrors(['structure_type' => 'Select a structure template to apply.'])
                ->withInput();
        }

        $subject->load(['course.department']);

        if (! empty($validated['structure_type'])) {
            $baselineFormula = $subject->department
                ? $this->ensureDepartmentFallback($subject->department, $periodContext)
                : $this->getGlobalFormula();

            $baselineFormula->loadMissing('weights');

            $this->applyStructureTypeToSubject($subject, $validated['structure_type'], $baselineFormula);
            $this->resetSubjectAssessmentsForNewStructure($subject);
            GradesFormulaService::flushCache();

            return redirect()
                ->route('vpaa.gradingConfiguration.subjects.show', array_merge([
                    'subject' => $subject->id,
                ], $this->formulaQueryParams()))
                ->with('success', 'Structure template applied to this subject.');
        }

        $selectedFormula = GradesFormula::with('weights')
            ->where('id', $validated['department_formula_id'])
            ->where('scope_level', 'department')
            ->first();

        if (! $selectedFormula || $selectedFormula->department_id !== $subject->department_id) {
            return back()
                ->withErrors(['department_formula_id' => 'Select a formula from this subject’s department.'])
                ->withInput();
        }

        $this->cloneFormulaToSubject($subject, $selectedFormula);
        GradesFormulaService::flushCache();

        return redirect()
            ->route('vpaa.gradingConfiguration.subjects.show', array_merge([
                'subject' => $subject->id,
            ], $this->formulaQueryParams()))
            ->with('success', 'Formula applied to this subject.');
    }

    public function removeSubjectFormula(Request $request, Subject $subject)
    {
        Gate::authorize('manage-grading-configuration');

        if ($subject->is_deleted) {
            abort(404);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];

        $subjectFormulaQuery = GradesFormula::where('subject_id', $subject->id)
            ->where('scope_level', 'subject');

        $subjectFormulaQuery = $this->applyPeriodFilters($subjectFormulaQuery, $selectedSemester, $selectedAcademicPeriodId);

        if ($selectedAcademicPeriodId) {
            $subjectFormulaQuery->orderByRaw('CASE WHEN academic_period_id = ? THEN 0 WHEN academic_period_id IS NULL THEN 1 ELSE 2 END', [$selectedAcademicPeriodId]);
        } else {
            $subjectFormulaQuery->orderByRaw('CASE WHEN academic_period_id IS NULL THEN 0 ELSE 1 END');
        }

        if ($selectedSemester) {
            $subjectFormulaQuery->orderByRaw('CASE WHEN semester = ? THEN 0 WHEN semester IS NULL THEN 1 ELSE 2 END', [$selectedSemester]);
        } else {
            $subjectFormulaQuery->orderByRaw('CASE WHEN semester IS NULL THEN 0 ELSE 1 END');
        }

        $subjectFormula = $subjectFormulaQuery->first();

        if (! $subjectFormula || ! $this->formulaMatchesContext($subjectFormula, $selectedSemester, $selectedAcademicPeriodId)) {
            $subjectFormula = GradesFormula::where('subject_id', $subject->id)
                ->where('scope_level', 'subject')
                ->orderByDesc('updated_at')
                ->first();
        }

        if (! $subjectFormula) {
            return redirect()
                ->route('vpaa.gradingConfiguration.subjects.show', array_merge([
                    'subject' => $subject->id,
                ], $this->formulaQueryParams()))
                ->with('success', 'Subject already inherits its department formula.');
        }

        DB::transaction(function () use ($subjectFormula) {
            $subjectFormula->delete();
        });

        GradesFormulaService::flushCache();

        return redirect()
            ->route('vpaa.gradingConfiguration.subjects.show', array_merge([
                'subject' => $subject->id,
            ], $this->formulaQueryParams()))
            ->with('success', 'Custom subject formula removed. This subject now inherits department settings.');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-grading-configuration');

        $scope = $request->input('scope_level');
        $passwordProvided = $request->filled('password');
        $requiresPassword = in_array($scope, ['global']);

        if ($requiresPassword || $passwordProvided) {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (! Hash::check($request->input('password'), Auth::user()->password)) {
                return back()
                    ->withErrors(['password' => 'The provided password is incorrect.'])
                    ->withInput();
            }
        }

        $scopeRules = [
            'scope_level' => ['required', Rule::in(['global', 'department', 'course', 'subject'])],
            'label' => ['nullable', 'string', 'max:255'],
            'base_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale_multiplier' => ['required', 'numeric', 'min:0', 'max:100'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'structure_type' => ['required', Rule::in(array_keys(FormulaStructure::getAllStructureDefinitions()))],
            'structure_config' => ['required', 'string'],
        ];

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];

        if ($scope === 'global') {
            // Global formulas are department-independent
            // No additional validation needed
        } elseif ($scope === 'department') {
            $scopeRules['department_id'] = [
                'required',
                'exists:departments,id',
            ];
            $scopeRules['is_department_fallback'] = ['nullable', 'boolean'];
        } elseif ($scope === 'course') {
            $scopeRules['course_id'] = [
                'required',
                'exists:courses,id',
            ];
        } elseif ($scope === 'subject') {
            $scopeRules['subject_id'] = [
                'required',
                'exists:subjects,id',
                Rule::unique('grades_formula', 'subject_id')->where(function ($query) use ($selectedSemester, $selectedAcademicPeriodId) {
                    $query->where('scope_level', 'subject');

                    if ($selectedSemester === null) {
                        $query->whereNull('semester');
                    } else {
                        $query->where('semester', $selectedSemester);
                    }

                    if ($selectedAcademicPeriodId === null) {
                        $query->whereNull('academic_period_id');
                    } else {
                        $query->where('academic_period_id', $selectedAcademicPeriodId);
                    }
                }),
            ];
        }

        $validated = $request->validate($scopeRules);

        if ($scope === 'course') {
            $existingCourseFormula = GradesFormula::where('course_id', $validated['course_id'] ?? null)
                ->where('scope_level', 'course')
                ->when($selectedSemester, fn ($q, $sem) => $q->where('semester', $sem), fn ($q) => $q->whereNull('semester'))
                ->when(
                    $selectedAcademicPeriodId !== null,
                    fn ($q) => $q->where('academic_period_id', $selectedAcademicPeriodId),
                    fn ($q) => $q->whereNull('academic_period_id')
                )
                ->first();

            if ($existingCourseFormula) {
                return $this->updateGradesFormula($request, $existingCourseFormula);
            }
        } elseif ($scope === 'subject') {
            $existingSubjectFormula = GradesFormula::where('subject_id', $validated['subject_id'] ?? null)
                ->where('scope_level', 'subject')
                ->when($selectedSemester, fn ($q, $sem) => $q->where('semester', $sem), fn ($q) => $q->whereNull('semester'))
                ->when(
                    $selectedAcademicPeriodId !== null,
                    fn ($q) => $q->where('academic_period_id', $selectedAcademicPeriodId),
                    fn ($q) => $q->whereNull('academic_period_id')
                )
                ->first();

            if ($existingSubjectFormula) {
                return $this->updateGradesFormula($request, $existingSubjectFormula);
            }
        }

        $isFallback = $scope === 'department' ? $request->boolean('is_department_fallback') : false;

        if (abs(($validated['base_score'] + $validated['scale_multiplier']) - 100) > 0.001) {
            return back()
                ->withErrors(['base_score' => 'Base score and scale multiplier must add up to 100 to keep the grading scale consistent.'])
                ->withInput();
        }
        try {
            $percentStructure = json_decode($validated['structure_config'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return back()
                ->withErrors(['structure_config' => 'Unable to read structure configuration payload.'])
                ->withInput();
        }

        if (! is_array($percentStructure)) {
            return back()
                ->withErrors(['structure_config' => 'Invalid structure configuration payload.'])
                ->withInput();
        }

        $normalizedStructure = FormulaStructure::fromPercentPayload($percentStructure);
        $structureErrors = FormulaStructure::validate($normalizedStructure);

        if (! empty($structureErrors)) {
            return back()
                ->withErrors(['structure_config' => implode(' ', $structureErrors)])
                ->withInput();
        }

        $flattenedWeights = collect(FormulaStructure::flattenWeights($normalizedStructure));

        if ($flattenedWeights->isEmpty()) {
            return back()
                ->withErrors(['structure_config' => 'The grade structure must include at least one assessment component.'])
                ->withInput();
        }

        $weights = $flattenedWeights
            ->map(fn ($entry) => [
                'activity_type' => $entry['activity_type'],
                'weight' => $entry['weight'],
            ]);

        $department = null;
        $course = null;
        $subject = null;

        if ($scope === 'global') {
            // Global formulas don't have department, course, or subject associations
        } elseif ($scope === 'department') {
            $department = Department::findOrFail($validated['department_id']);
        } elseif ($scope === 'course') {
            $course = Course::with('department')->findOrFail($validated['course_id']);
            $department = $course->department;
        } elseif ($scope === 'subject') {
            $subject = Subject::with(['course.department'])->findOrFail($validated['subject_id']);
            $course = $subject->course;
            $department = $subject->department ?? $course?->department;

            // Password is NOT required when creating subject formulas
            // It is only required when applying templates (handled in applySubjectFormula)
            // This allows instructors to create and fine-tune formulas without password prompts
        }

        $label = $validated['label'] ?? match ($scope) {
            'global' => 'Institution Fallback Formula',
            'department' => ($department?->department_description ?? 'Department').' Formula',
            'course' => ($course?->course_code ? $course->course_code.' · ' : '').($course?->course_description ?? 'Course').' Formula',
            'subject' => ($subject?->subject_code ? $subject->subject_code.' · ' : '').($subject?->subject_description ?? 'Subject').' Formula',
            default => 'Custom Formula',
        };

        DB::transaction(function () use (
            $scope,
            $department,
            $course,
            $subject,
            $label,
            $validated,
            $weights,
            $isFallback,
            $selectedSemester,
            $selectedAcademicPeriodId,
            $normalizedStructure
        ) {
            if ($scope === 'department' && $department) {
                if ($isFallback) {
                    GradesFormula::where('department_id', $department->id)
                        ->where('scope_level', 'department')
                        ->when(
                            $selectedSemester !== null,
                            fn ($q) => $q->where('semester', $selectedSemester),
                            fn ($q) => $q->whereNull('semester')
                        )
                        ->when(
                            $selectedAcademicPeriodId !== null,
                            fn ($q) => $q->where('academic_period_id', $selectedAcademicPeriodId),
                            fn ($q) => $q->whereNull('academic_period_id')
                        )
                        ->update(['is_department_fallback' => false]);
                }
            }

            $name = $this->generateFormulaName($scope, $department, $course, $subject, $selectedAcademicPeriodId, $selectedSemester);

            $formula = GradesFormula::create([
                'name' => $name,
                'label' => $label,
                'scope_level' => $scope,
                'department_id' => $department?->id,
                'course_id' => $scope === 'course' ? optional($course)->id : null,
                'subject_id' => $scope === 'subject' ? optional($subject)->id : null,
                'semester' => $selectedSemester,
                'academic_period_id' => $selectedAcademicPeriodId,
                'base_score' => $validated['base_score'],
                'scale_multiplier' => $validated['scale_multiplier'],
                'passing_grade' => $validated['passing_grade'],
                'structure_type' => $validated['structure_type'],
                'structure_config' => $normalizedStructure,
                'is_department_fallback' => $scope === 'department' ? $isFallback : false,
            ]);

            $formula->weights()->createMany($weights->all());
        });

        GradesFormulaService::flushCache();

        if ($scope === 'global') {
            return redirect()
                ->route('vpaa.gradingConfiguration.index', array_merge($this->formulaQueryParams(), ['view' => 'formulas']))
                ->with('success', 'Institution fallback formula saved successfully.');
        }

        $redirectRoute = match ($scope) {
            'department' => $department
                ? route('vpaa.gradingConfiguration.departments.show', array_merge(['department' => $department->id], $this->formulaQueryParams()))
                : route('vpaa.gradingConfiguration.index', $this->formulaQueryParams()),
            'course' => ($department && $course)
                ? route('vpaa.gradingConfiguration.courses.show', array_merge(['department' => $department->id, 'course' => $course->id], $this->formulaQueryParams()))
                : route('vpaa.gradingConfiguration.index', $this->formulaQueryParams()),
            'subject' => $subject
                ? route('vpaa.gradingConfiguration.subjects.show', array_merge(['subject' => $subject->id], $this->formulaQueryParams()))
                : route('vpaa.gradingConfiguration.index', $this->formulaQueryParams()),
            default => route('vpaa.gradingConfiguration.index', $this->formulaQueryParams()),
        };

        return redirect($redirectRoute)
            ->with('success', 'Grades formula saved successfully.');
    }

    public function update(Request $request, GradesFormula $formula)
    {
        Gate::authorize('manage-grading-configuration');

        $scope = $formula->scope_level ?? 'department';

        // Validate password for global formulas
        if ($scope === 'global') {
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            if (! Hash::check($request->input('password'), Auth::user()->password)) {
                return back()
                    ->withErrors(['password' => 'The provided password is incorrect.'])
                    ->withInput();
            }
        }

        if ($scope === 'subject') {
            $formula->loadMissing(['subject.course.department']);
        }

        $periodContext = $this->resolveFormulaPeriodContext();
        $selectedSemester = $periodContext['semester'];
        $selectedAcademicPeriodId = $periodContext['academic_period_id'];
        $contextExplicit = $request->hasAny(['semester', 'academic_year', 'academic_period_id']);

        if (! $contextExplicit) {
            $selectedSemester ??= $formula->semester;
            $selectedAcademicPeriodId ??= $formula->academic_period_id;
        }

        if ($selectedSemester === null) {
            $selectedAcademicPeriodId = null;
        }

        // Password is NOT required when editing an existing subject formula
        // It is only required when creating/applying a new formula (handled in storeGradesFormula/applySubjectFormula)
        // This allows instructors to fine-tune weights without password prompts

        $rules = [
            'label' => ['nullable', 'string', 'max:255'],
            'base_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale_multiplier' => ['required', 'numeric', 'min:0', 'max:100'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'structure_type' => ['required', Rule::in(array_keys(FormulaStructure::getAllStructureDefinitions()))],
            'structure_config' => ['required', 'string'],
        ];

        if ($scope === 'department') {
            $rules['is_department_fallback'] = ['nullable', 'boolean'];
        }
        $validated = $request->validate($rules);

        $isFallback = $scope === 'department'
            ? $request->boolean('is_department_fallback', $formula->is_department_fallback)
            : $formula->is_department_fallback;

        if ($scope === 'department' && ! $isFallback && $formula->is_department_fallback) {
            $otherFallbackExists = GradesFormula::where('department_id', $formula->department_id)
                ->where('scope_level', 'department')
                ->where('id', '!=', $formula->id)
                ->where('is_department_fallback', true)
                ->exists();

            if (! $otherFallbackExists) {
                return back()
                    ->withErrors(['is_department_fallback' => 'Each department needs at least one fallback formula.'])
                    ->withInput();
            }
        }

        if (abs(($validated['base_score'] + $validated['scale_multiplier']) - 100) > 0.001) {
            return back()
                ->withErrors(['base_score' => 'Base score and scale multiplier must add up to 100 to keep the grading scale consistent.'])
                ->withInput();
        }
        try {
            $percentStructure = json_decode($validated['structure_config'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return back()
                ->withErrors(['structure_config' => 'Unable to read structure configuration payload.'])
                ->withInput();
        }

        if (! is_array($percentStructure)) {
            return back()
                ->withErrors(['structure_config' => 'Invalid structure configuration payload.'])
                ->withInput();
        }

        $normalizedStructure = FormulaStructure::fromPercentPayload($percentStructure);
        $structureErrors = FormulaStructure::validate($normalizedStructure);

        if (! empty($structureErrors)) {
            return back()
                ->withErrors(['structure_config' => implode(' ', $structureErrors)])
                ->withInput();
        }

        $flattenedWeights = collect(FormulaStructure::flattenWeights($normalizedStructure));

        if ($flattenedWeights->isEmpty()) {
            return back()
                ->withErrors(['structure_config' => 'The grade structure must include at least one assessment component.'])
                ->withInput();
        }

        $weights = $flattenedWeights
            ->map(fn ($entry) => [
                'activity_type' => $entry['activity_type'],
                'weight' => $entry['weight'],
            ]);

        $label = $validated['label'] ?? $formula->label;

        DB::transaction(function () use ($formula, $label, $validated, $weights, $scope, $isFallback, $selectedSemester, $selectedAcademicPeriodId, $normalizedStructure) {
            if ($scope === 'department' && $isFallback) {
                GradesFormula::where('department_id', $formula->department_id)
                    ->where('scope_level', 'department')
                    ->when(
                        $selectedSemester !== null,
                        fn ($q) => $q->where('semester', $selectedSemester),
                        fn ($q) => $q->whereNull('semester')
                    )
                    ->when(
                        $selectedAcademicPeriodId !== null,
                        fn ($q) => $q->where('academic_period_id', $selectedAcademicPeriodId),
                        fn ($q) => $q->whereNull('academic_period_id')
                    )
                    ->where('id', '!=', $formula->id)
                    ->update(['is_department_fallback' => false]);
            }

            if ($scope !== 'course') {
                $formula->course_id = null;
            }

            $formula->fill([
                'label' => $label,
                'base_score' => $validated['base_score'],
                'scale_multiplier' => $validated['scale_multiplier'],
                'passing_grade' => $validated['passing_grade'],
                'semester' => $selectedSemester,
                'academic_period_id' => $selectedAcademicPeriodId,
                'is_department_fallback' => $scope === 'department' ? $isFallback : $formula->is_department_fallback,
                'structure_type' => $validated['structure_type'],
                'structure_config' => $normalizedStructure,
            ]);
            $formula->save();

            $formula->weights()->delete();
            $formula->weights()->createMany($weights->all());
        });

        GradesFormulaService::flushCache();

        $formula->loadMissing(['department', 'course', 'subject']);

        $queryParams = $this->formulaQueryParams();

        if ($scope === 'global') {
            return redirect()
                ->route('vpaa.gradingConfiguration.index', array_merge($queryParams, ['view' => 'formulas']))
                ->with('success', 'Institution fallback formula updated successfully.');
        }

        $redirectRoute = match ($scope) {
            'department' => $formula->department
                ? route('vpaa.gradingConfiguration.departments.show', array_merge(['department' => $formula->department->id], $queryParams))
                : route('vpaa.gradingConfiguration.index', $queryParams),
            'course' => ($formula->department && $formula->course)
                ? route('vpaa.gradingConfiguration.courses.show', array_merge(['department' => $formula->department->id, 'course' => $formula->course->id], $queryParams))
                : route('vpaa.gradingConfiguration.index', $queryParams),
            'subject' => $formula->subject
                ? route('vpaa.gradingConfiguration.subjects.show', array_merge(['subject' => $formula->subject->id], $queryParams))
                : route('vpaa.gradingConfiguration.index', $queryParams),
            default => route('vpaa.gradingConfiguration.index', $queryParams),
        };

        return redirect()->to($redirectRoute)
            ->with('success', 'Grades formula updated successfully.');
    }
}
