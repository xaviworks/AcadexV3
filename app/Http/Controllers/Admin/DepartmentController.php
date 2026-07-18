<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        Gate::authorize('admin');

        $departments = Department::where('is_deleted', false)
            ->orderBy('department_code')
            ->get();

        return view('admin.departments', compact('departments'));
    }

    public function create()
    {
        Gate::authorize('admin');

        return view('admin.create-department');
    }

    public function validatePassword(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            // Mark password as confirmed for a short window (5 minutes)
            session(['department_password_confirmed_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Password confirmed successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        // For AJAX requests, validate password confirmation
        if ($request->expectsJson() || $request->ajax()) {
            $request->validate([
                'department_code' => ['required', 'string', 'max:50', Rule::unique('departments')->where('is_deleted', false)],
                'department_description' => 'required|string|max:255',
                'password' => 'required|string',
            ]);

            if (! Hash::check($request->password, Auth::user()->password)) {
                return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
            }

            $department = Department::create([
                'department_code' => strtoupper($request->department_code),
                'department_description' => $request->department_description,
                'is_deleted' => false,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department added successfully.',
                'department' => $department,
            ]);
        }

        // Traditional form submission (fallback)
        $request->validate([
            'department_code' => ['required', 'string', 'max:50', Rule::unique('departments')->where('is_deleted', false)],
            'department_description' => 'required|string|max:255',
        ]);

        Department::create([
            'department_code' => strtoupper($request->department_code),
            'department_description' => $request->department_description,
            'is_deleted' => false,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.departments')->with('success', 'Department added successfully.');
    }

    public function update(Request $request, Department $department)
    {
        Gate::authorize('admin');

        if ($department->is_deleted) {
            abort(404);
        }

        $request->validate([
            'department_code' => ['required', 'string', 'max:50', Rule::unique('departments')->where('is_deleted', false)->ignore($department->id)],
            'department_description' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        $department->update([
            'department_code' => strtoupper($request->department_code),
            'department_description' => $request->department_description,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully.',
            'department' => $department->fresh(),
        ]);
    }

    public function destroy(Request $request, Department $department)
    {
        Gate::authorize('admin');

        if ($department->is_deleted) {
            abort(404);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password. Please try again.'], 422);
        }

        // Check if department has associated courses or users
        $hasCourses = Course::where('department_id', $department->id)->where('is_deleted', false)->exists();
        $hasUsers = \App\Models\User::where('department_id', $department->id)->exists();

        if ($hasCourses) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department. It has associated courses. Please remove or reassign them first.',
            ], 422);
        }

        if ($hasUsers) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department. It has associated users. Please remove or reassign them first.',
            ], 422);
        }

        // Soft delete
        $department->update([
            'is_deleted' => true,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully.',
        ]);
    }
}
