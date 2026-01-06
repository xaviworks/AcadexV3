<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Course;
use App\Models\TermGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InstructorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Instructor dashboard
    public function dashboard()
    {
        Gate::authorize('instructor');
        $instructor = Auth::user();

        // Redirect to the main dashboard route which will render the instructor dashboard
        // (DashboardController::index handles role detection and instructor dashboard rendering)
        return redirect()->route('dashboard');
    }

    // Manage Students Page (with subject grade status labels)
    public function index(Request $request)
    {
        Gate::authorize('instructor');

        $academicPeriodId = session('active_academic_period_id');
        $term = $request->query('term', 'prelim');

        $subjects = collect();

        if ($academicPeriodId) {
            $subjects = Subject::where(function($query) use ($academicPeriodId) {
                $query->where('instructor_id', Auth::id())
                      ->orWhereHas('instructors', function($q) {
                          $q->where('instructor_id', Auth::id());
                      });
            })
            ->where('is_deleted', false)
            ->where('academic_period_id', $academicPeriodId)
            ->withCount('students')
            ->get();

            // Optimize: Fetch graded counts for all subjects at once (prevent N+1)
            $gradedCounts = TermGrade::whereIn('subject_id', $subjects->pluck('id'))
                ->where('term', $term)
                ->select('subject_id', DB::raw('COUNT(DISTINCT student_id) as graded_count'))
                ->groupBy('subject_id')
                ->pluck('graded_count', 'subject_id');

            foreach ($subjects as $subject) {
                $totalStudents = $subject->students_count;
                $graded = $gradedCounts[$subject->id] ?? 0;

                $subject->grade_status = match (true) {
                    $graded === 0 => 'not_started',
                    $graded < $totalStudents => 'pending',
                    default => 'completed'
                };
            }
        }

        $courses = Cache::remember('courses:all', 3600, fn() => Course::all());
        $students = collect();

        if ($request->filled('subject_id')) {
            $subject = Subject::findOrFail($request->subject_id);

            // Allow access if instructor is primary instructor or assigned via pivot
            $isPrimary = $subject->instructor_id === Auth::id();
            $isPivotAssigned = $subject->instructors()->where('instructor_id', Auth::id())->exists();
            if (!$isPrimary && !$isPivotAssigned) {
                abort(403, 'Unauthorized access to subject.');
            }

            $students = $subject->students()
                ->where('students.is_deleted', 0)
                ->get();
        }

        return view('instructor.manage-students', compact('subjects', 'students', 'courses'));
    }

    // Shared helper for term mapping
    public static function getTermId($term)
    {
        return [
            'prelim' => 1,
            'midterm' => 2,
            'prefinal' => 3,
            'final' => 4,
        ][$term] ?? null;
    }
}
