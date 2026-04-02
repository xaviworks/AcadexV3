<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\ProgramLearningOutcome;
use App\Models\ProgramLearningOutcomeMapping;
use App\Services\CourseOutcomeReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'academic.period.set']);
    }

    /**
     * VPAA view: Program Learning Outcome summary for a selected program in a department.
     * GET /vpaa/reports/co-program?department_id=<id>&course_id=<id>
     */
    public function vpaaDepartment(Request $request, CourseOutcomeReportingService $service)
    {
        $departmentId = (int)$request->input('department_id', 0);
        $courseId = (int)$request->input('course_id', 0);
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);

        // Step 1: Department chooser
        if (!$departmentId) {
            $departments = Department::where('is_deleted', false)
                ->select('id', 'department_code', 'department_description')
                ->orderBy('department_description')
                ->get();

            return view('vpaa.reports.co-program-departments', [
                'departments' => $departments,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $department = Department::where('is_deleted', false)
            ->select('id', 'department_code', 'department_description')
            ->findOrFail($departmentId);

        // Step 2: Program chooser inside selected department
        if (!$courseId) {
            $courses = Course::where('department_id', $department->id)
                ->where('is_deleted', false)
                ->orderBy('course_code')
                ->get(['id', 'course_code', 'course_description', 'department_id']);

            return view('vpaa.reports.co-program-courses', [
                'department' => $department,
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        // Step 3: Program-level PLO summary for selected program
        $course = Course::with('department')
            ->where('department_id', $department->id)
            ->where('is_deleted', false)
            ->findOrFail($courseId);

        $ploSummary = $service->aggregateProgramLearningOutcomes($course->id, $periodId, true);
        $byProgram = [
            $course->id => [
                'program' => $course,
                'plos' => $ploSummary['results'],
            ],
        ];

        return view('vpaa.reports.co-program', [
            'department' => $department,
            'program' => $course,
            'byProgram' => $byProgram,
            'ploDefinitions' => $ploSummary['definitions'],
            'activePloDefinitions' => $ploSummary['activeDefinitions'],
            'ploMappings' => $ploSummary['mappings'],
            'availableCoCodes' => $ploSummary['availableCoCodes'],
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    /**
     * GE Coordinator view: CO compliance for GE subjects across all programs for the active academic period.
     * Shows all programs, but only counts CO attainment from GE subjects within each program.
     * GET /gecoordinator/reports/co-program
     */
    public function geCoordinatorProgram(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;
        
        $departmentId = 1; // GE department
        $department = Department::find($departmentId);
        
        // Get CO data for GE subjects across all programs
        $byCourse = $service->aggregateGESubjectsAcrossCourses($periodId);

        return view('gecoordinator.reports.co-program', [
            'department' => $department,
            'byCourse' => $byCourse,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    /**
     * Chairperson view: CO compliance for their own assigned course for the active academic period.
     * GET /chairperson/reports/co-program
     */
    public function chairProgram(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = session('active_academic_period_id');
        $period = $periodId ? AcademicPeriod::find($periodId) : null;
        $course = $this->resolveChairpersonCourse();
        $ploSummary = $service->aggregateProgramLearningOutcomes($course->id, $periodId, true);

        $byProgram = [
            $course->id => [
                'program' => $course,
                'plos' => $ploSummary['results'],
            ],
        ];

        return view('chairperson.reports.co-program', [
            'department' => $course?->department, // Still pass department for display context
            'program' => $course,
            'byProgram' => $byProgram,
            'ploDefinitions' => $ploSummary['definitions'],
            'activePloDefinitions' => $ploSummary['activeDefinitions'],
            'ploMappings' => $ploSummary['mappings'],
            'ploMappingCourseOutcomeIds' => $ploSummary['mappingExpandedCourseOutcomeIds'] ?? [],
            'availableCoCodes' => $ploSummary['availableCoCodes'],
            'availableCourseOutcomeRows' => $ploSummary['availableCourseOutcomeRows'] ?? [],
            'outcomeCodePrefix' => $ploSummary['outcomeCodePrefix']
                ?? $service->deriveProgramOutcomePrefix((string) $course->course_code),
            'defaultOutcomeCount' => CourseOutcomeReportingService::DEFAULT_PLO_COUNT,
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    public function saveChairProgramPlos(Request $request, CourseOutcomeReportingService $service)
    {
        $course = $this->resolveChairpersonCourse();
        $service->ensureDefaultProgramLearningOutcomes($course->id);
        $outcomePrefix = $service->deriveProgramOutcomePrefix((string) $course->course_code);

        $validator = Validator::make($request->all(), [
            'plos' => ['required', 'array'],
            'plos.*.id' => ['nullable', 'integer'],
            'plos.*.code' => ['nullable', 'string', 'max:20'],
            'plos.*.title' => ['nullable', 'string', 'max:255'],
            'plos.*.is_active' => ['nullable', 'boolean'],
            'plos.*.delete' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request, $outcomePrefix) {
            $submitted = collect($request->input('plos', []));
            $activeRows = $submitted->filter(function ($plo) {
                return !filter_var($plo['delete'] ?? false, FILTER_VALIDATE_BOOL);
            })->values();

            if ($activeRows->isEmpty()) {
                $validator->errors()->add('plos', 'At least one PLO must remain configured.');
                return;
            }

            if ($activeRows->count() > CourseOutcomeReportingService::MAX_PLO_COUNT) {
                $validator->errors()->add('plos', 'You can configure a maximum of 20 PLOs.');
            }

            $codes = [];
            $codePattern = '/^' . preg_quote($outcomePrefix, '/') . '(0[1-9]|1[0-9]|20)$/';
            foreach ($activeRows as $index => $plo) {
                $code = strtoupper(trim((string) ($plo['code'] ?? '')));
                $title = trim((string) ($plo['title'] ?? ''));

                if ($code === '' || !preg_match($codePattern, $code)) {
                    $validator->errors()->add(
                        "plos.$index.code",
                        sprintf('Outcome code must follow %s01 to %s20.', $outcomePrefix, $outcomePrefix)
                    );
                }

                if ($title === '') {
                    $validator->errors()->add("plos.$index.title", 'PLO title is required.');
                }

                if (in_array($code, $codes, true)) {
                    $validator->errors()->add("plos.$index.code", 'PLO codes must be unique.');
                }

                $codes[] = $code;
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('openPloModal', true)
                ->with('ploTab', 'definitions');
        }

        $existing = ProgramLearningOutcome::where('course_id', $course->id)
            ->where('is_deleted', false)
            ->get()
            ->keyBy('id');

        $submittedRows = collect($request->input('plos', []))->values();

        foreach ($submittedRows as $index => $payload) {
            $id = $payload['id'] ?? null;
            $shouldDelete = filter_var($payload['delete'] ?? false, FILTER_VALIDATE_BOOL);

            if ($shouldDelete) {
                if ($id && $existing->has((int) $id)) {
                    $existing[(int) $id]->update(['is_deleted' => true]);
                    ProgramLearningOutcomeMapping::where('program_learning_outcome_id', (int) $id)->delete();
                }
                continue;
            }

            $attributes = [
                'course_id' => $course->id,
                'plo_code' => strtoupper(trim((string) $payload['code'])),
                'title' => trim((string) $payload['title']),
                'display_order' => $index + 1,
                'is_active' => filter_var($payload['is_active'] ?? false, FILTER_VALIDATE_BOOL),
                'is_deleted' => false,
            ];

            if ($id && $existing->has((int) $id)) {
                $existing[(int) $id]->update($attributes);
            } else {
                ProgramLearningOutcome::create($attributes);
            }
        }

        return back()->with('success', 'Program Learning Outcomes updated successfully.');
    }

    public function saveChairProgramPloMappings(Request $request, CourseOutcomeReportingService $service)
    {
        $course = $this->resolveChairpersonCourse();
        $periodId = session('active_academic_period_id');
        $ploIds = $service->getProgramLearningOutcomes($course->id)->pluck('id')->all();
        $availableRows = collect($service->getAvailableCourseOutcomeRows($course->id, $periodId, true));
        $availableRowsById = $availableRows->keyBy('id');
        $availableCourseOutcomeIds = $availableRows->pluck('id')->map(fn ($id) => (int) $id)->all();
        $availableCoCodes = $availableRows->pluck('co_code')->filter()->map(fn ($code) => strtoupper((string) $code))->unique()->values()->all();

        $validator = Validator::make($request->all(), [
            'mappings' => ['nullable', 'array'],
        ]);

        $validator->after(function ($validator) use ($request, $ploIds, $availableCourseOutcomeIds, $availableCoCodes) {
            $allowedPloIds = collect($ploIds)->map(fn ($id) => (int) $id)->all();

             if (empty($availableCourseOutcomeIds) && !empty($request->input('mappings', []))) {
                $validator->errors()->add('mappings', 'No course outcomes are available to link for this program yet.');
                return;
            }

            foreach ($request->input('mappings', []) as $ploId => $mappings) {
                if (!in_array((int) $ploId, $allowedPloIds, true)) {
                    $validator->errors()->add('mappings', 'Invalid PLO selected for mapping.');
                    continue;
                }

                foreach ((array) $mappings as $index => $mappingValue) {
                    $normalizedValue = is_string($mappingValue)
                        ? trim($mappingValue)
                        : $mappingValue;

                    $isCourseOutcomeId = is_numeric($normalizedValue)
                        && in_array((int) $normalizedValue, $availableCourseOutcomeIds, true);

                    $isLegacyCoCode = is_string($normalizedValue)
                        && in_array(strtoupper($normalizedValue), $availableCoCodes, true);

                    if (!$isCourseOutcomeId && !$isLegacyCoCode) {
                        $validator->errors()->add(
                            "mappings.$ploId.$index",
                            'Mappings can only use available course outcomes for this program.'
                        );
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('openPloModal', true)
                ->with('ploTab', 'mapping');
        }

        ProgramLearningOutcomeMapping::where('course_id', $course->id)->delete();

        foreach ($request->input('mappings', []) as $ploId => $mappings) {
            $normalizedMappings = collect((array) $mappings)
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            foreach ($normalizedMappings as $mappingValue) {
                if (is_numeric($mappingValue) && in_array((int) $mappingValue, $availableCourseOutcomeIds, true)) {
                    $courseOutcomeId = (int) $mappingValue;
                    $courseOutcomeRow = $availableRowsById->get($courseOutcomeId);

                    if (!$courseOutcomeRow) {
                        continue;
                    }

                    ProgramLearningOutcomeMapping::create([
                        'course_id' => $course->id,
                        'program_learning_outcome_id' => (int) $ploId,
                        'course_outcome_id' => $courseOutcomeId,
                        'co_code' => $courseOutcomeRow['co_code'],
                    ]);

                    continue;
                }

                if (is_string($mappingValue)) {
                    $coCode = strtoupper($mappingValue);

                    if (!in_array($coCode, $availableCoCodes, true)) {
                        continue;
                    }

                    ProgramLearningOutcomeMapping::create([
                        'course_id' => $course->id,
                        'program_learning_outcome_id' => (int) $ploId,
                        'co_code' => $coCode,
                    ]);
                }
            }
        }

        return back()
            ->with('openPloModal', true)
            ->with('ploTab', 'mapping')
            ->with('success', 'CO to PLO mapping updated successfully.');
    }

    /**
     * Dean view: Program Learning Outcome summary for a selected program in their department.
     * GET /dean/reports/co-program?course_id=<id>
     */
    public function deanProgram(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);
        $courseId = (int) $request->input('course_id', 0);
        $user = auth()->user();
        $departmentId = $user?->department_id;

        if (!$departmentId) {
            abort(403, 'You are not assigned to any department.');
        }

        $department = Department::where('is_deleted', false)
            ->select('id', 'department_code', 'department_description')
            ->findOrFail($departmentId);

        if (!$courseId) {
            $courses = Course::where('department_id', $department->id)
                ->where('is_deleted', false)
                ->orderBy('course_code')
                ->get(['id', 'course_code', 'course_description', 'department_id']);

            return view('dean.reports.co-program-courses', [
                'department' => $department,
                'courses' => $courses,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $course = Course::with('department')
            ->where('department_id', $department->id)
            ->where('is_deleted', false)
            ->findOrFail($courseId);

        $ploSummary = $service->aggregateProgramLearningOutcomes($course->id, $periodId, true);
        $byProgram = [
            $course->id => [
                'program' => $course,
                'plos' => $ploSummary['results'],
            ],
        ];

        return view('dean.reports.co-program', [
            'department' => $department,
            'program' => $course,
            'byProgram' => $byProgram,
            'ploDefinitions' => $ploSummary['definitions'],
            'activePloDefinitions' => $ploSummary['activeDefinitions'],
            'ploMappings' => $ploSummary['mappings'],
            'availableCoCodes' => $ploSummary['availableCoCodes'],
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    private function resolveChairpersonCourse(): Course
    {
        $courseId = auth()->user()?->course_id;

        if (!$courseId) {
            abort(403, 'You are not assigned to any course.');
        }

        return Course::with('department')->findOrFail($courseId);
    }

    private function resolveRequiredAcademicPeriodId(): int
    {
        $sessionPeriodId = session('active_academic_period_id');
        if ($sessionPeriodId && AcademicPeriod::where('id', $sessionPeriodId)->where('is_deleted', false)->exists()) {
            return (int) $sessionPeriodId;
        }

        $latestPeriod = AcademicPeriod::where('is_deleted', false)
            ->orderByDesc('academic_year')
            ->orderByRaw("CASE semester WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN 'Summer' THEN 3 ELSE 4 END")
            ->first();

        if ($latestPeriod) {
            session(['active_academic_period_id' => $latestPeriod->id]);

            return (int) $latestPeriod->id;
        }

        abort(403, 'No active academic period is available.');
    }
}
