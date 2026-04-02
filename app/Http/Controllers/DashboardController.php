<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\
{
    Student, 
    Subject, 
    TermGrade, 
    FinalGrade,
    User,
    UnverifiedUser,
    UserLog,
    Course,
    Department,
};

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 0) {
            return $this->instructorDashboard();
        }

        if ($user->isChairperson()) {
            return $this->chairpersonDashboard();
        }

        if ($user->isGECoordinator()) {
            return $this->geCoordinatorDashboard();
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard($request);
        }

        if ($user->role === 2) { // Dean
            return $this->deanDashboard();
        }

        if ($user->isVPAA()) {
            return redirect()->route('vpaa.dashboard');
        }

        abort(403, 'Unauthorized access.');
    }

    private function instructorDashboard()
    {
        if (!session()->has('active_academic_period_id')) {
            return redirect()->route('select.academicPeriod');
        }

        $academicPeriodId = session('active_academic_period_id');
        $instructorId = Auth::id();

        $subjects = $this->getInstructorSubjects($instructorId, $academicPeriodId);
        $dashboardData = $this->getInstructorDashboardData($subjects, $academicPeriodId);
        $subjectCharts = $this->generateSubjectCharts($subjects);

        return view('dashboard.instructor', $dashboardData + ['subjectCharts' => $subjectCharts]);
    }

    private function getInstructorSubjects($instructorId, $academicPeriodId)
    {
        // Use a JOIN instead of orWhereHas (avoids slow EXISTS subquery)
        $primaryIds = Subject::where('instructor_id', $instructorId)
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false)
            ->pluck('id');

        $pivotIds = DB::table('instructor_subject')
            ->join('subjects', 'subjects.id', '=', 'instructor_subject.subject_id')
            ->where('instructor_subject.instructor_id', $instructorId)
            ->where('subjects.academic_period_id', $academicPeriodId)
            ->where('subjects.is_deleted', false)
            ->pluck('instructor_subject.subject_id');

        $allIds = $primaryIds->merge($pivotIds)->unique();

        return Subject::whereIn('id', $allIds)
            ->where('is_deleted', false)
            ->withCount(['students' => fn($q) => $q->where('students.is_deleted', false)])
            ->get();
    }

    private function getInstructorDashboardData($subjects, $academicPeriodId)
    {
        $enrolledSubjectsCount = $subjects->count();
        $subjectIds = $subjects->pluck('id');

        // Single query for student count across all subjects
        $instructorStudents = DB::table('student_subjects')
            ->join('students', 'students.id', '=', 'student_subjects.student_id')
            ->whereIn('student_subjects.subject_id', $subjectIds)
            ->where('students.is_deleted', false)
            ->distinct('students.id')
            ->count('students.id');

        // Single query for pass/fail counts
        $finalGradeCounts = FinalGrade::whereIn('subject_id', $subjectIds)
            ->where('academic_period_id', $academicPeriodId)
            ->selectRaw('remarks, COUNT(*) as count')
            ->groupBy('remarks')
            ->pluck('count', 'remarks');

        $totalPassedStudents = $finalGradeCounts['Passed'] ?? 0;
        $totalFailedStudents = $finalGradeCounts['Failed'] ?? 0;

        $termCompletions = $this->calculateTermCompletions($subjects, $subjectIds);

        return compact(
            'instructorStudents',
            'enrolledSubjectsCount',
            'totalPassedStudents',
            'totalFailedStudents',
            'termCompletions'
        );
    }

    private function calculateTermCompletions($subjects, $subjectIds = null)
    {
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];
        $subjectIds = $subjectIds ?? $subjects->pluck('id');

        // Single batched query instead of N×4 individual queries
        $gradedBySubjectTerm = TermGrade::whereIn('subject_id', $subjectIds)
            ->selectRaw('subject_id, term_id, COUNT(DISTINCT student_id) as graded_count')
            ->groupBy('subject_id', 'term_id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => $rows->pluck('graded_count', 'term_id'));

        $studentCounts = $subjects->pluck('students_count', 'id');

        $termCompletions = [];
        foreach ($terms as $term) {
            $termId = $this->getTermId($term);
            $total = $studentCounts->sum();
            $graded = $gradedBySubjectTerm->map(fn($termRows) => $termRows->get($termId, 0))->sum();

            $termCompletions[$term] = [
                'graded' => $graded,
                'total'  => $total,
            ];
        }

        return $termCompletions;
    }

    private function generateSubjectCharts($subjects)
    {
        $terms = ['prelim', 'midterm', 'prefinal', 'final'];
        $subjectIds = $subjects->pluck('id');

        // Single batched query instead of N×4 individual queries
        $gradedBySubjectTerm = TermGrade::whereIn('subject_id', $subjectIds)
            ->selectRaw('subject_id, term_id, COUNT(DISTINCT student_id) as graded_count')
            ->groupBy('subject_id', 'term_id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => $rows->pluck('graded_count', 'term_id'));

        $subjectCharts = [];

        foreach ($subjects as $subject) {
            $termsData = [];
            $termPercentages = [];
            $subjectGraded = $gradedBySubjectTerm->get($subject->id, collect());
            $studentCount  = $subject->students_count;

            foreach ($terms as $term) {
                $termId    = $this->getTermId($term);
                $gradedCount = $subjectGraded->get($termId, 0);
                $percentage  = $studentCount > 0 ? round(($gradedCount / $studentCount) * 100, 2) : 0;

                $termsData[$term] = [
                    'graded'     => $gradedCount,
                    'total'      => $studentCount,
                    'percentage' => $percentage,
                ];
                $termPercentages[] = $percentage;
            }

            $subjectCharts[] = [
                'code'         => $subject->subject_code,
                'description'  => $subject->subject_description,
                'terms'        => $termsData,
                'termPercentages' => $termPercentages,
            ];
        }

        return $subjectCharts;
    }

    private function chairpersonDashboard()
    {
        if (!session()->has('active_academic_period_id')) {
            return redirect()->route('select.academicPeriod');
        }

        $departmentId = Auth::user()->department_id;
        $academicPeriodId = session('active_academic_period_id');
        $chairpersonCourseId = Auth::user()->course_id;

        // One aggregated query instead of 3 separate User COUNT queries
        $instructorStats = User::where("role", 0)
            ->where("department_id", $departmentId)
            ->where("course_id", $chairpersonCourseId)
            ->selectRaw('
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_count
            ')
            ->first();

        $countActiveInstructors   = (int) ($instructorStats->active_count ?? 0);
        $countInactiveInstructors = (int) ($instructorStats->inactive_count ?? 0);
        $countInstructors         = $countActiveInstructors;

        // JOIN instead of costly EXISTS subquery (whereHas)
        $countStudents = Student::join('student_subjects', 'students.id', '=', 'student_subjects.student_id')
            ->join('subjects', 'student_subjects.subject_id', '=', 'subjects.id')
            ->where('students.department_id', $departmentId)
            ->where('students.is_deleted', false)
            ->where('subjects.academic_period_id', $academicPeriodId)
            ->distinct('students.id')
            ->count('students.id');

        $countCourses = Subject::where('department_id', $departmentId)
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false)
            ->distinct('course_id')
            ->count('course_id');

        $countUnverifiedInstructors = UnverifiedUser::where("department_id", $departmentId)->count();

        $data = compact(
            'countInstructors',
            'countStudents',
            'countCourses',
            'countActiveInstructors',
            'countInactiveInstructors',
            'countUnverifiedInstructors'
        );

        return view('dashboard.chairperson', $data);
    }

    private function adminDashboard(Request $request)
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());
        $selectedYear = $request->query('year', now()->year);
        $yearRange = range(now()->year, now()->year - 10);

        $loginStats = $this->getLoginStats($selectedDate);
        $monthlyStats = $this->getMonthlyLoginStats($selectedYear);

        return view('dashboard.admin', array_merge([
            'totalUsers' => User::count(),
            'selectedDate' => $selectedDate,
            'selectedYear' => $selectedYear,
            'yearRange' => $yearRange,
        ], $loginStats, $monthlyStats));
    }

    private function getLoginStats($selectedDate)
    {
        $hours = range(0, 23);
        
        $successfulLogins = UserLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->where('event_type', 'login')
            ->whereDate('created_at', $selectedDate)
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total', 'hour');

        $failedLogins = UserLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->where('event_type', 'failed_login')
            ->whereDate('created_at', $selectedDate)
            ->groupByRaw('HOUR(created_at)')
            ->pluck('total', 'hour');

        $successfulData = array_map(fn($hour) => $successfulLogins[$hour] ?? 0, $hours);
        $failedData = array_map(fn($hour) => $failedLogins[$hour] ?? 0, $hours);

        return [
            'loginCount' => array_sum($successfulData),
            'failedLoginCount' => array_sum($failedData),
            'successfulData' => $successfulData,
            'failedData' => $failedData,
        ];
    }

    private function getMonthlyLoginStats($selectedYear)
    {
        $monthlySuccessfulLogins = UserLog::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->where('event_type', 'login')
            ->whereYear('created_at', $selectedYear)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $monthlyFailedLogins = UserLog::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->where('event_type', 'failed_login')
            ->whereYear('created_at', $selectedYear)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $monthlySuccessfulData = array_map(fn($month) => $monthlySuccessfulLogins[$month] ?? 0, range(1, 12));
        $monthlyFailedData = array_map(fn($month) => $monthlyFailedLogins[$month] ?? 0, range(1, 12));

        return [
            'monthlySuccessfulData' => $monthlySuccessfulData,
            'monthlyFailedData' => $monthlyFailedData,
        ];
    }

    private function deanDashboard()
    {
        $academicPeriodId = $this->resolveLatestAcademicPeriodIdForDean();
        $deanDepartmentId = Auth::user()?->department_id;

        if (! $academicPeriodId || ! $deanDepartmentId) {
            return view('dashboard.dean', [
                'studentsPerDepartment' => collect(),
                'totalInstructors' => 0,
                'studentsPerCourse' => collect(),
            ]);
        }

        $studentsPerDepartment = Student::join('departments', 'students.department_id', '=', 'departments.id')
            ->where('students.is_deleted', false)
            ->select('departments.department_description as department_name', DB::raw('count(*) as total'))
            ->where('students.academic_period_id', $academicPeriodId)
            ->where('students.department_id', $deanDepartmentId)
            ->groupBy('students.department_id', 'departments.department_description')
            ->pluck('total', 'department_name');

        $studentsPerCourse = Student::join('courses', 'students.course_id', '=', 'courses.id')
            ->where('students.is_deleted', false)
            ->where('courses.is_deleted', false)
            ->where('courses.department_id', $deanDepartmentId)
            ->select('courses.course_code', 'courses.course_description', DB::raw('count(*) as total'))
            ->where('students.academic_period_id', $academicPeriodId)
            ->where('students.department_id', $deanDepartmentId)
            ->groupBy('students.course_id', 'courses.course_code', 'courses.course_description')
            ->pluck('total', 'courses.course_code');

        $teachingInstructors = $this->countTeachingInstructorsForDepartment($academicPeriodId, $deanDepartmentId);
        $totalInstructors = $teachingInstructors > 0
            ? $teachingInstructors
            : User::where('role', 0)
                ->where('is_active', true)
                ->where('department_id', $deanDepartmentId)
                ->count();

        return view('dashboard.dean', [
            'studentsPerDepartment' => $studentsPerDepartment,
            'totalInstructors' => $totalInstructors,
            'studentsPerCourse' => $studentsPerCourse
        ]);
    }

    private function countTeachingInstructorsForDepartment(?int $academicPeriodId, ?int $departmentId): int
    {
        if (! $academicPeriodId || ! $departmentId) {
            return 0;
        }

        return User::where('users.role', 0)
            ->where('users.is_active', true)
            ->where('users.department_id', $departmentId)
            ->where(function ($teachingQuery) use ($academicPeriodId, $departmentId) {
                $teachingQuery->whereExists(function ($subQuery) use ($academicPeriodId, $departmentId) {
                    $subQuery->select(DB::raw(1))
                        ->from('subjects')
                        ->whereColumn('subjects.instructor_id', 'users.id')
                        ->where('subjects.department_id', $departmentId)
                        ->where('subjects.academic_period_id', $academicPeriodId)
                        ->where('subjects.is_deleted', false);
                })->orWhereExists(function ($subQuery) use ($academicPeriodId, $departmentId) {
                    $subQuery->select(DB::raw(1))
                        ->from('instructor_subject')
                        ->join('subjects', 'subjects.id', '=', 'instructor_subject.subject_id')
                        ->whereColumn('instructor_subject.instructor_id', 'users.id')
                        ->where('subjects.department_id', $departmentId)
                        ->where('subjects.academic_period_id', $academicPeriodId)
                        ->where('subjects.is_deleted', false);
                });
            })
            ->count();
    }

    private function resolveLatestAcademicPeriodIdForDean(): ?int
    {
        $sessionPeriodId = session('active_academic_period_id');
        if ($sessionPeriodId) {
            return (int) $sessionPeriodId;
        }

        $latestPeriod = \App\Models\AcademicPeriod::where('is_deleted', false)
            ->orderByDesc('academic_year')
            ->orderByRaw("CASE semester WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN 'Summer' THEN 3 ELSE 4 END")
            ->first();

        if ($latestPeriod) {
            session(['active_academic_period_id' => $latestPeriod->id]);

            return (int) $latestPeriod->id;
        }

        return null;
    }

    private function geCoordinatorDashboard()
    {
        if (!session()->has('active_academic_period_id')) {
            return redirect()->route('select.academicPeriod');
        }

        $academicPeriodId = session('active_academic_period_id');
        
        // Get GE department
        $geDepartment = Department::where('department_code', 'GE')->first();
        
        if (!$geDepartment) {
            return back()->with('error', 'GE department not found. Please contact administrator.');
        }

        // Count all instructors in GE department or approved to teach GE subjects
        $countInstructors = User::where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id) // Always count GE department instructors
                      ->orWhere("can_teach_ge", true) // Count those who can currently teach GE
                      ->orWhere(function($subQuery) {
                          // Count inactive instructors who previously had approved GE requests
                          $subQuery->where('is_active', false)
                                   ->whereHas('geSubjectRequests', function($requestQuery) {
                                       $requestQuery->where('status', 'approved');
                                   });
                      });
            })
            ->count();
            
        $countActiveInstructors = User::where("is_active", 1)
            ->where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id)
                      ->orWhere("can_teach_ge", true);
            })
            ->count();
            
        $countInactiveInstructors = User::where("is_active", 0)
            ->where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id)
                      ->orWhere(function($subQuery) {
                          // Count inactive instructors who previously had approved GE requests
                          $subQuery->whereHas('geSubjectRequests', function($requestQuery) {
                              $requestQuery->where('status', 'approved');
                          });
                      });
            })
            ->count();
            
        $countPendingInstructors = UnverifiedUser::where("department_id", $geDepartment->id)
            ->count();

        $data = [
            "countInstructors" => $countInstructors,
            "countStudents" => Student::whereHas('subjects', function($query) use ($geDepartment, $academicPeriodId) {
                    $query->where('department_id', $geDepartment->id)
                          ->where('academic_period_id', $academicPeriodId);
                })
                ->where("is_deleted", false)
                ->distinct()
                ->count("students.id"),
            "countCourses" => Subject::where("department_id", $geDepartment->id)
                ->where("is_deleted", false)
                ->where('academic_period_id', $academicPeriodId)
                ->distinct('subject_code')
                ->count(),
            "countActiveInstructors" => $countActiveInstructors,
            "countInactiveInstructors" => $countInactiveInstructors,
            "countPendingInstructors" => $countPendingInstructors,
            "activePercentage" => $countInstructors > 0 ? round(($countActiveInstructors / $countInstructors) * 100, 1) : 0,
            "inactivePercentage" => $countInstructors > 0 ? round(($countInactiveInstructors / $countInstructors) * 100, 1) : 0,
            "pendingPercentage" => ($countInstructors + $countPendingInstructors) > 0 ? round(($countPendingInstructors / ($countInstructors + $countPendingInstructors)) * 100, 1) : 0,
        ];

        return view('dashboard.gecoordinator', $data);
    }

    private function getTermId($term)
    {
        return [
            'prelim' => 1,
            'midterm' => 2,
            'prefinal' => 3,
            'final' => 4,
        ][$term] ?? null;
    }

    /**
     * Return dashboard data as JSON for real-time polling.
     * Dispatches based on the authenticated user's role.
     */
    public function pollData(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if ($user->role === 0) {
            return $this->pollInstructorData();
        }

        if ($user->isChairperson()) {
            return $this->pollChairpersonData();
        }

        if ($user->isGECoordinator()) {
            return $this->pollGECoordinatorData();
        }

        if ($user->isAdmin()) {
            return $this->pollAdminData($request);
        }

        if ($user->role === 2) {
            return $this->pollDeanData();
        }

        return response()->json(['error' => 'Unknown role'], 403);
    }

    private function pollInstructorData(): \Illuminate\Http\JsonResponse
    {
        if (!session()->has('active_academic_period_id')) {
            return response()->json(['error' => 'No academic period set'], 400);
        }

        $academicPeriodId = session('active_academic_period_id');
        $instructorId = Auth::id();
        $subjects = $this->getInstructorSubjects($instructorId, $academicPeriodId);
        $dashboardData = $this->getInstructorDashboardData($subjects, $academicPeriodId);
        $subjectCharts = $this->generateSubjectCharts($subjects);

        return response()->json([
            'instructorStudents' => $dashboardData['instructorStudents'],
            'enrolledSubjectsCount' => $dashboardData['enrolledSubjectsCount'],
            'totalPassedStudents' => $dashboardData['totalPassedStudents'],
            'totalFailedStudents' => $dashboardData['totalFailedStudents'],
            'termCompletions' => $dashboardData['termCompletions'],
            'subjectCharts' => $subjectCharts,
        ]);
    }

    private function pollChairpersonData(): \Illuminate\Http\JsonResponse
    {
        if (!session()->has('active_academic_period_id')) {
            return response()->json(['error' => 'No academic period set'], 400);
        }

        $departmentId = Auth::user()->department_id;
        $academicPeriodId = session('active_academic_period_id');
        $chairpersonCourseId = Auth::user()->course_id;

        // One aggregated query instead of 3 separate User COUNT queries
        $instructorStats = User::where("role", 0)
            ->where("department_id", $departmentId)
            ->where("course_id", $chairpersonCourseId)
            ->selectRaw('
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_count
            ')
            ->first();

        $countActiveInstructors   = (int) ($instructorStats->active_count ?? 0);
        $countInactiveInstructors = (int) ($instructorStats->inactive_count ?? 0);
        $countInstructors         = $countActiveInstructors;

        // JOIN instead of costly EXISTS subquery (whereHas)
        $countStudents = Student::join('student_subjects', 'students.id', '=', 'student_subjects.student_id')
            ->join('subjects', 'student_subjects.subject_id', '=', 'subjects.id')
            ->where('students.department_id', $departmentId)
            ->where('students.is_deleted', false)
            ->where('subjects.academic_period_id', $academicPeriodId)
            ->distinct('students.id')
            ->count('students.id');

        $countCourses = Subject::where('department_id', $departmentId)
            ->where('academic_period_id', $academicPeriodId)
            ->where('is_deleted', false)
            ->distinct('course_id')
            ->count('course_id');

        $countUnverifiedInstructors = UnverifiedUser::where("department_id", $departmentId)->count();

        return response()->json([
            'countInstructors' => $countInstructors,
            'countStudents' => $countStudents,
            'countCourses' => $countCourses,
            'countActiveInstructors' => $countActiveInstructors,
            'countInactiveInstructors' => $countInactiveInstructors,
            'countUnverifiedInstructors' => $countUnverifiedInstructors,
            'activePercentage' => $countInstructors > 0 ? round(($countActiveInstructors / $countInstructors) * 100, 1) : 0,
            'inactivePercentage' => $countInstructors > 0 ? round(($countInactiveInstructors / $countInstructors) * 100, 1) : 0,
            'pendingPercentage' => $countInstructors > 0 ? round(($countUnverifiedInstructors / $countInstructors) * 100, 1) : 0,
        ]);
    }

    private function pollGECoordinatorData(): \Illuminate\Http\JsonResponse
    {
        if (!session()->has('active_academic_period_id')) {
            return response()->json(['error' => 'No academic period set'], 400);
        }

        $academicPeriodId = session('active_academic_period_id');
        $geDepartment = Department::where('department_code', 'GE')->first();

        if (!$geDepartment) {
            return response()->json(['error' => 'GE department not found'], 404);
        }

        $countInstructors = User::where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id)
                      ->orWhere("can_teach_ge", true)
                      ->orWhere(function($subQuery) {
                          $subQuery->where('is_active', false)
                                   ->whereHas('geSubjectRequests', function($requestQuery) {
                                       $requestQuery->where('status', 'approved');
                                   });
                      });
            })
            ->count();

        $countActiveInstructors = User::where("is_active", 1)
            ->where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id)
                      ->orWhere("can_teach_ge", true);
            })
            ->count();

        $countInactiveInstructors = User::where("is_active", 0)
            ->where("role", 0)
            ->where(function($query) use ($geDepartment) {
                $query->where("department_id", $geDepartment->id)
                      ->orWhere(function($subQuery) {
                          $subQuery->whereHas('geSubjectRequests', function($requestQuery) {
                              $requestQuery->where('status', 'approved');
                          });
                      });
            })
            ->count();

        $countPendingInstructors = UnverifiedUser::where("department_id", $geDepartment->id)->count();

        $countStudents = Student::whereHas('subjects', function($query) use ($geDepartment, $academicPeriodId) {
                $query->where('department_id', $geDepartment->id)
                      ->where('academic_period_id', $academicPeriodId);
            })
            ->where("is_deleted", false)
            ->distinct()
            ->count("students.id");

        $countCourses = Subject::where("department_id", $geDepartment->id)
            ->where("is_deleted", false)
            ->where('academic_period_id', $academicPeriodId)
            ->distinct('subject_code')
            ->count();

        return response()->json([
            'countInstructors' => $countInstructors,
            'countStudents' => $countStudents,
            'countCourses' => $countCourses,
            'countActiveInstructors' => $countActiveInstructors,
            'countInactiveInstructors' => $countInactiveInstructors,
            'countPendingInstructors' => $countPendingInstructors,
            'activePercentage' => $countInstructors > 0 ? round(($countActiveInstructors / $countInstructors) * 100, 1) : 0,
            'inactivePercentage' => $countInstructors > 0 ? round(($countInactiveInstructors / $countInstructors) * 100, 1) : 0,
            'pendingPercentage' => ($countInstructors + $countPendingInstructors) > 0 ? round(($countPendingInstructors / ($countInstructors + $countPendingInstructors)) * 100, 1) : 0,
        ]);
    }

    private function pollAdminData(Request $request): \Illuminate\Http\JsonResponse
    {
        $selectedDate = $request->query('date', \Carbon\Carbon::today()->toDateString());
        $selectedYear = $request->query('year', now()->year);

        $loginStats = $this->getLoginStats($selectedDate);
        $monthlyStats = $this->getMonthlyLoginStats($selectedYear);

        return response()->json([
            'totalUsers' => User::count(),
            'loginCount' => $loginStats['loginCount'],
            'failedLoginCount' => $loginStats['failedLoginCount'],
            'successfulData' => $loginStats['successfulData'],
            'failedData' => $loginStats['failedData'],
            'monthlySuccessfulData' => $monthlyStats['monthlySuccessfulData'],
            'monthlyFailedData' => $monthlyStats['monthlyFailedData'],
            'selectedDate' => $selectedDate,
            'selectedYear' => $selectedYear,
        ]);
    }

    private function pollDeanData(): \Illuminate\Http\JsonResponse
    {
        $academicPeriodId = $this->resolveLatestAcademicPeriodIdForDean();
        $deanDepartmentId = Auth::user()?->department_id;

        if (! $academicPeriodId || ! $deanDepartmentId) {
            return response()->json([
                'totalStudents' => 0,
                'totalInstructors' => 0,
                'totalCourses' => 0,
                'totalDepartments' => 0,
                'studentsPerDepartment' => collect(),
                'studentsPerCourse' => collect(),
            ]);
        }

        $studentsPerDepartment = Student::join('departments', 'students.department_id', '=', 'departments.id')
            ->where('students.is_deleted', false)
            ->select('departments.department_description as department_name', DB::raw('count(*) as total'))
            ->where('students.academic_period_id', $academicPeriodId)
            ->where('students.department_id', $deanDepartmentId)
            ->groupBy('students.department_id', 'departments.department_description')
            ->pluck('total', 'department_name');

        $studentsPerCourse = Student::join('courses', 'students.course_id', '=', 'courses.id')
            ->where('students.is_deleted', false)
            ->where('courses.is_deleted', false)
            ->where('courses.department_id', $deanDepartmentId)
            ->select('courses.course_code', 'courses.course_description', DB::raw('count(*) as total'))
            ->where('students.academic_period_id', $academicPeriodId)
            ->where('students.department_id', $deanDepartmentId)
            ->groupBy('students.course_id', 'courses.course_code', 'courses.course_description')
            ->pluck('total', 'courses.course_code');

        $teachingInstructors = $this->countTeachingInstructorsForDepartment($academicPeriodId, $deanDepartmentId);
        $totalInstructors = $teachingInstructors > 0
            ? $teachingInstructors
            : User::where('role', 0)
                ->where('is_active', true)
                ->where('department_id', $deanDepartmentId)
                ->count();

        return response()->json([
            'totalStudents' => $studentsPerDepartment->sum(),
            'totalInstructors' => $totalInstructors,
            'totalCourses' => $studentsPerCourse->count(),
            'totalDepartments' => $studentsPerDepartment->count(),
            'studentsPerDepartment' => $studentsPerDepartment,
            'studentsPerCourse' => $studentsPerCourse,
        ]);
    }
}
