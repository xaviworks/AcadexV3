<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('admin');

        $courses = Course::with('department')
            ->where('is_deleted', false)
            ->orderBy('course_code')
            ->get();

        // Pass departments for the modal
        $departments = Department::where('is_deleted', false)
            ->orderBy('department_code')
            ->get();

        return view('admin.courses', compact('courses', 'departments'));
    }

    public function create()
    {
        Gate::authorize('admin');

        $departments = Department::where('is_deleted', false)
            ->orderBy('department_code')
            ->get();

        return view('admin.create-course', compact('departments'));
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $rules = [
            'course_code' => ['required', 'string', 'max:50', Rule::unique('courses')->where('is_deleted', false)],
            'course_description' => 'required|string|max:255',
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where('is_deleted', false),
            ],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            $request->validate($rules + ['password' => 'required|string']);

            if (! Hash::check($request->password, Auth::user()->password)) {
                return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
            }

            $course = Course::create([
                'course_code' => trim((string) $request->course_code),
                'course_description' => trim((string) $request->course_description),
                'department_id' => $request->department_id,
                'is_deleted' => false,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ])->load('department');

            return response()->json([
                'success' => true,
                'message' => 'Program added successfully.',
                'course' => $course,
            ]);
        }

        $request->validate($rules);

        Course::create([
            'course_code' => trim((string) $request->course_code),
            'course_description' => trim((string) $request->course_description),
            'department_id' => $request->department_id,
            'is_deleted' => false,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.courses')->with('success', 'Program added successfully.');
    }

    public function update(Request $request, Course $course)
    {
        Gate::authorize('admin');

        if ($course->is_deleted) {
            abort(404);
        }

        $request->validate([
            'course_code' => ['required', 'string', 'max:50', Rule::unique('courses')->where('is_deleted', false)->ignore($course->id)],
            'course_description' => 'required|string|max:255',
            'department_id' => [
                'required',
                Rule::exists('departments', 'id')->where('is_deleted', false),
            ],
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        $course->update([
            'course_code' => trim((string) $request->course_code),
            'course_description' => trim((string) $request->course_description),
            'department_id' => $request->department_id,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Program updated successfully.',
            'course' => $course->fresh(['department']),
        ]);
    }

    public function destroy(Request $request, Course $course)
    {
        Gate::authorize('admin');

        if ($course->is_deleted) {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        $hasSubjects = Subject::where('course_id', $course->id)->where('is_deleted', false)->exists();
        $hasUsers = User::where('course_id', $course->id)->exists();
        $hasStudents = \App\Models\Student::where('course_id', $course->id)->exists();
        $hasCurriculums = \App\Models\Curriculum::where('course_id', $course->id)->exists();

        if ($hasSubjects) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program. It has associated subjects. Please remove or reassign them first.',
            ], 422);
        }

        if ($hasUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program. It has associated users. Please remove or reassign them first.',
            ], 422);
        }

        if ($hasStudents) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program. It has associated students. Please remove or reassign them first.',
            ], 422);
        }

        if ($hasCurriculums) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program. It has associated curriculum records. Please remove or reassign them first.',
            ], 422);
        }

        $course->update([
            'is_deleted' => true,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully.',
        ]);
    }
}
