<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\Department;
use App\Models\FinalGrade;
use App\Models\GradesFormula;
use App\Models\Subject;
use App\Models\TermGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('admin');

        $subjects = Subject::with(['department', 'course', 'academicPeriod'])
            ->where('is_deleted', false)
            ->orderBy('subject_code')
            ->get();

        $departments = Department::where('is_deleted', false)
            ->orderBy('department_code')
            ->get();

        $courses = Course::where('is_deleted', false)
            ->orderBy('course_code')
            ->get();

        $academicPeriods = AcademicPeriod::orderBy('academic_year', 'desc')
            ->orderBy('semester')
            ->get();

        return view('admin.subjects', compact('subjects', 'departments', 'courses', 'academicPeriods'));
    }

    public function create()
    {
        Gate::authorize('admin');

        $departments = Department::where('is_deleted', false)->orderBy('department_code')->get();
        $courses = Course::where('is_deleted', false)->orderBy('course_code')->get();
        $academicPeriods = AcademicPeriod::orderBy('academic_year', 'desc')->orderBy('semester')->get();

        return view('admin.create-subject', compact('departments', 'courses', 'academicPeriods'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $rules = [
            'subject_code' => ['required', 'string', 'max:255', Rule::unique('subjects', 'subject_code')->where('is_deleted', false)],
            'subject_description' => 'required|string|max:255',
            'units' => 'required|integer|min:1|max:6',
            'year_level' => 'required|integer|min:1|max:5',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'department_id' => ['required', Rule::exists('departments', 'id')->where('is_deleted', false)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('is_deleted', false)],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            $request->validate($rules + ['password' => 'required|string']);

            if (! Hash::check($request->password, Auth::user()->password)) {
                return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
            }

            $subject = Subject::create([
                'subject_code' => trim((string) $request->subject_code),
                'subject_description' => trim((string) $request->subject_description),
                'units' => (int) $request->units,
                'year_level' => (int) $request->year_level,
                'academic_period_id' => $request->academic_period_id,
                'department_id' => $request->department_id,
                'course_id' => $request->course_id,
                'is_deleted' => false,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ])->load(['department', 'course', 'academicPeriod']);

            return response()->json([
                'success' => true,
                'message' => 'Subject added successfully.',
                'subject' => $subject,
            ]);
        }

        $request->validate($rules);

        Subject::create([
            'subject_code' => trim((string) $request->subject_code),
            'subject_description' => trim((string) $request->subject_description),
            'units' => (int) $request->units,
            'year_level' => (int) $request->year_level,
            'academic_period_id' => $request->academic_period_id,
            'department_id' => $request->department_id,
            'course_id' => $request->course_id,
            'is_deleted' => false,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.subjects')->with('success', 'Subject added successfully.');
    }

    public function update(Request $request, Subject $subject)
    {
        Gate::authorize('admin');

        if ($subject->is_deleted) {
            abort(404);
        }

        $request->validate([
            'subject_code' => ['required', 'string', 'max:255', Rule::unique('subjects', 'subject_code')->where('is_deleted', false)->ignore($subject->id)],
            'subject_description' => 'required|string|max:255',
            'units' => 'required|integer|min:1|max:6',
            'year_level' => 'required|integer|min:1|max:5',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'department_id' => ['required', Rule::exists('departments', 'id')->where('is_deleted', false)],
            'course_id' => ['required', Rule::exists('courses', 'id')->where('is_deleted', false)],
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        $subject->update([
            'subject_code' => trim((string) $request->subject_code),
            'subject_description' => trim((string) $request->subject_description),
            'units' => (int) $request->units,
            'year_level' => (int) $request->year_level,
            'academic_period_id' => $request->academic_period_id,
            'department_id' => $request->department_id,
            'course_id' => $request->course_id,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully.',
            'subject' => $subject->fresh(['department', 'course', 'academicPeriod']),
        ]);
    }

    public function destroy(Request $request, Subject $subject)
    {
        Gate::authorize('admin');

        if ($subject->is_deleted) {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        $hasStudentEnrollments = \App\Models\StudentSubject::where('subject_id', $subject->id)
            ->where('is_deleted', false)
            ->exists();
        $hasInstructorAssignments = DB::table('instructor_subject')
            ->where('subject_id', $subject->id)
            ->exists();
        $hasCourseOutcomes = \App\Models\CourseOutcomes::where('subject_id', $subject->id)
            ->where('is_deleted', false)
            ->exists();
        $hasActivities = Activity::where('subject_id', $subject->id)
            ->where('is_deleted', false)
            ->exists();
        $hasTermGrades = TermGrade::where('subject_id', $subject->id)->exists();
        $hasFinalGrades = FinalGrade::where('subject_id', $subject->id)->exists();
        $hasSubjectFormula = GradesFormula::where('subject_id', $subject->id)->exists();
        $hasAttainmentLevel = \App\Models\SubjectAttainmentLevel::where('subject_id', $subject->id)->exists();

        if ($hasStudentEnrollments) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It still has enrolled students.',
            ], 422);
        }

        if ($hasInstructorAssignments) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It is still assigned to instructors.',
            ], 422);
        }

        if ($hasCourseOutcomes) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It already has course outcomes.',
            ], 422);
        }

        if ($hasActivities) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It already has activities.',
            ], 422);
        }

        if ($hasTermGrades || $hasFinalGrades) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It already has recorded grades.',
            ], 422);
        }

        if ($hasSubjectFormula) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It already has a grading formula configuration.',
            ], 422);
        }

        if ($hasAttainmentLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It already has attainment settings.',
            ], 422);
        }

        $subject->update([
            'is_deleted' => true,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully.',
        ]);
    }
}
