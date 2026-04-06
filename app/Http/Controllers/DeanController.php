<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\FinalGrade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================
    // View Instructors under Dean
    // ============================

    public function viewInstructors()
    {
        Gate::authorize('dean');

        $departmentId = Auth::user()->department_id;
        $academicPeriodId = session('active_academic_period_id');

        $instructors = User::where('role', 0) // Instructor role
            ->where('department_id', Auth::user()->department_id)
            ->where('is_active', true)
            ->where(function ($teachingQuery) use ($academicPeriodId, $departmentId) {
                if (! $academicPeriodId) {
                    $teachingQuery->whereRaw('1 = 0');

                    return;
                }

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
            ->orderBy('last_name')
            ->get();

        return view('dean.instructors', compact('instructors'));
    }

    // ============================
    // View Students under Dean
    // ============================

    public function viewStudents(Request $request)
    {
        Gate::authorize('dean');

        $selectedCourseId = $request->input('course_id');
        $academicPeriodId = session('active_academic_period_id');
        
        $query = Student::with('course')
            ->where('department_id', Auth::user()->department_id)
            ->where('is_deleted', false)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($academicPeriodId) {
            $query->where('academic_period_id', $academicPeriodId);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($selectedCourseId) {
            $query->where('course_id', $selectedCourseId);
        }

        $students = $query->get();
        $courses = Course::where('department_id', Auth::user()->department_id)
            ->where('is_deleted', false)
            ->orderBy('course_code')
            ->get();

        $droppedStudentIds = $academicPeriodId
            ? DB::table('student_subjects')
                ->join('subjects', 'student_subjects.subject_id', '=', 'subjects.id')
                ->where('subjects.academic_period_id', $academicPeriodId)
                ->where('student_subjects.is_deleted', true)
                ->pluck('student_subjects.student_id')
                ->flip()
            : collect();

        return view('dean.students', compact('students', 'courses', 'selectedCourseId', 'droppedStudentIds'));
    }

    // ============================
    // View Final Grades by Course
    // ============================

    public function viewGrades(Request $request)
    {
        Gate::authorize('dean');
    
        $departmentId = Auth::user()->department_id;
        $academicPeriodId = session('active_academic_period_id'); // Assuming academic period is stored in session
    
        // List of courses in the dean's department
        $courses = Course::where('department_id', $departmentId)
            ->where('is_deleted', false)
            ->orderBy('course_code')
            ->get();
    
        // Initialize collections
        $students = collect();
        $finalGrades = collect();
        $instructors = collect();
        $subjects = collect();
    
        // Step 1: Filter by selected course
        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
    
            // Step 2: Get instructors for the selected course
            $instructors = User::where('role', 0) // role 0 = instructor
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->where(function ($teachingQuery) use ($courseId, $academicPeriodId, $departmentId) {
                    if (! $academicPeriodId) {
                        $teachingQuery->whereRaw('1 = 0');

                        return;
                    }

                    $teachingQuery->whereExists(function ($subQuery) use ($courseId, $academicPeriodId, $departmentId) {
                        $subQuery->select(DB::raw(1))
                            ->from('subjects')
                            ->whereColumn('subjects.instructor_id', 'users.id')
                            ->where('subjects.department_id', $departmentId)
                            ->where('subjects.course_id', $courseId)
                            ->where('subjects.academic_period_id', $academicPeriodId)
                            ->where('subjects.is_deleted', false);
                    })->orWhereExists(function ($subQuery) use ($courseId, $academicPeriodId, $departmentId) {
                        $subQuery->select(DB::raw(1))
                            ->from('instructor_subject')
                            ->join('subjects', 'subjects.id', '=', 'instructor_subject.subject_id')
                            ->whereColumn('instructor_subject.instructor_id', 'users.id')
                            ->where('subjects.department_id', $departmentId)
                            ->where('subjects.course_id', $courseId)
                            ->where('subjects.academic_period_id', $academicPeriodId)
                            ->where('subjects.is_deleted', false);
                    });
                })
                ->orderBy('last_name')
                ->get();
    
            // Step 3: Get subjects for the selected course and instructor
            if ($request->filled('instructor_id') && $academicPeriodId) {
                $instructorId = $request->input('instructor_id');
    
                $subjects = Subject::where([
                        ['department_id', $departmentId],
                        ['academic_period_id', $academicPeriodId],
                        ['course_id', $courseId],
                        ['is_deleted', false],
                    ])
                    ->where(function ($query) use ($instructorId) {
                        $query->where('instructor_id', $instructorId)
                            ->orWhereHas('instructors', function ($subQuery) use ($instructorId) {
                                $subQuery->where('users.id', $instructorId);
                            });
                    })
                    ->orderBy('subject_code')
                    ->get();
    
                // Step 4: Get students for the selected subject and course
                if ($request->filled('subject_id')) {
                    $subjectId = $request->input('subject_id');
    
                    // Ensure the selected subject belongs to the selected instructor and scope.
                    $subject = Subject::with('students')
                        ->where('id', $subjectId)
                        ->where('department_id', $departmentId)
                        ->where('academic_period_id', $academicPeriodId)
                        ->where('course_id', $courseId)
                        ->where('is_deleted', false)
                        ->where(function ($query) use ($instructorId) {
                            $query->where('instructor_id', $instructorId)
                                ->orWhereHas('instructors', function ($subQuery) use ($instructorId) {
                                    $subQuery->where('users.id', $instructorId);
                                });
                        })
                        ->firstOrFail();
    
                    $students = $subject->studentsWithEnrollmentStatus()
                        ->where('students.department_id', $departmentId)
                        ->where('students.course_id', $courseId)
                        ->where('students.is_deleted', false)
                        ->orderBy('students.last_name')
                        ->orderBy('students.first_name')
                        ->get();
    
                    // Get final grades for the students in the selected subject
                    $finalGrades = FinalGrade::where('subject_id', $subjectId)
                        ->whereIn('student_id', $students->pluck('id'))
                        ->get()
                        ->keyBy('student_id');
                }
            }
        }
    
        return view('dean.grades', compact(
            'courses',
            'students',
            'finalGrades',
            'instructors',
            'subjects'
        ));
    }
    
}
