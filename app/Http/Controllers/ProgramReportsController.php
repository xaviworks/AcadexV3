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
     * VPAA/Chair view: CO compliance per program (department) summarized per course for the active academic period.
     * GET /vpaa/reports/co-program?department_id=<id>
     */
    public function vpaaDepartment(Request $request, CourseOutcomeReportingService $service)
    {
        $departmentId = (int)$request->input('department_id', 0);
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);

        // If no department selected, show departments list to choose from
        if (!$departmentId) {
            $departments = \App\Models\Department::where('is_deleted', false)
                ->select('id', 'department_code', 'department_description')
                ->orderBy('department_description')
                ->get();

            return view('vpaa.reports.co-program-departments', [
                'departments' => $departments,
                'academicYear' => $period?->academic_year,
                'semester' => $period?->semester,
            ]);
        }

        $department = Department::select('id', 'department_code', 'department_description')
            ->findOrFail($departmentId);
        $byCourse = $service->aggregateDepartmentByCourse($departmentId, $periodId);

        return view('vpaa.reports.co-program', [
            'department' => $department,
            'byCourse' => $byCourse,
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
            'availableCoCodes' => $ploSummary['availableCoCodes'],
            'academicYear' => $period?->academic_year,
            'semester' => $period?->semester,
        ]);
    }

    public function saveChairProgramPlos(Request $request, CourseOutcomeReportingService $service)
    {
        $course = $this->resolveChairpersonCourse();
        $service->ensureDefaultProgramLearningOutcomes($course->id);

        $validator = Validator::make($request->all(), [
            'plos' => ['required', 'array'],
            'plos.*.id' => ['nullable', 'integer'],
            'plos.*.code' => ['nullable', 'string', 'max:20'],
            'plos.*.title' => ['nullable', 'string', 'max:255'],
            'plos.*.is_active' => ['nullable', 'boolean'],
            'plos.*.delete' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
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
            foreach ($activeRows as $index => $plo) {
                $code = strtoupper(trim((string) ($plo['code'] ?? '')));
                $title = trim((string) ($plo['title'] ?? ''));

                if ($code === '' || !preg_match('/^PLO([1-9]|1[0-9]|20)$/', $code)) {
                    $validator->errors()->add("plos.$index.code", 'PLO code must be between PLO1 and PLO20.');
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
        $availableCoCodes = $service->getAvailableCoCodes($course->id, $periodId, true);

        $validator = Validator::make($request->all(), [
            'mappings' => ['nullable', 'array'],
        ]);

        $validator->after(function ($validator) use ($request, $ploIds, $availableCoCodes) {
            $allowedPloIds = collect($ploIds)->map(fn ($id) => (int) $id)->all();

             if (empty($availableCoCodes) && !empty($request->input('mappings', []))) {
                $validator->errors()->add('mappings', 'No course outcomes are available to link for this program yet.');
                return;
            }

            foreach ($request->input('mappings', []) as $ploId => $coCodes) {
                if (!in_array((int) $ploId, $allowedPloIds, true)) {
                    $validator->errors()->add('mappings', 'Invalid PLO selected for mapping.');
                    continue;
                }

                foreach ((array) $coCodes as $index => $coCode) {
                    if (!in_array($coCode, $availableCoCodes, true)) {
                        $validator->errors()->add(
                            "mappings.$ploId.$index",
                            'Mappings can only use the available CO slots for this program.'
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

        foreach ($request->input('mappings', []) as $ploId => $coCodes) {
            foreach (collect((array) $coCodes)->unique()->values() as $coCode) {
                ProgramLearningOutcomeMapping::create([
                    'course_id' => $course->id,
                    'program_learning_outcome_id' => (int) $ploId,
                    'co_code' => $coCode,
                ]);
            }
        }

        return back()
            ->with('openPloModal', true)
            ->with('ploTab', 'mapping')
            ->with('success', 'CO to PLO mapping updated successfully.');
    }

    /**
     * Dean view: CO compliance for their department for the active academic period.
     * GET /dean/reports/co-program
     */
    public function deanProgram(Request $request, CourseOutcomeReportingService $service)
    {
        $periodId = $this->resolveRequiredAcademicPeriodId();
        $period = AcademicPeriod::find($periodId);
        $user = auth()->user();
        $departmentId = $user?->department_id;

        if (!$departmentId) {
            abort(403, 'You are not assigned to any department.');
        }

        $department = Department::find($departmentId);
        $byCourse = $service->aggregateDepartmentByCourse($departmentId, $periodId);

        return view('dean.reports.co-program', [
            'department' => $department,
            'byCourse' => $byCourse,
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
