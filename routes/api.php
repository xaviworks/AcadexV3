<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use App\Support\Organization\GEContext;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/department/{id}/courses', function ($id) {
    try {
        $department = Department::query()
            ->where('is_deleted', false)
            ->findOrFail($id);

        $courses = Course::query()
            ->where('department_id', $department->id)
            ->where('is_deleted', false)
            ->get()
            ->keyBy('id');

        $aseDepartmentId = GEContext::aseDepartmentId();
        $geCourseId = GEContext::geCourseId();

        // Presentation compatibility: expose GE as an ASE program option.
        if (
            $aseDepartmentId !== null
            && (int) $department->id === (int) $aseDepartmentId
            && $geCourseId !== null
            && !$courses->has($geCourseId)
        ) {
            $geCourse = Course::query()
                ->where('id', $geCourseId)
                ->where('is_deleted', false)
                ->first();

            if ($geCourse) {
                $courses->put($geCourse->id, $geCourse);
            }
        }

        $courses = $courses
            ->sortBy('course_code')
            ->values()
            ->map(function ($course) use ($geCourseId) {
            return [
                'id' => $course->id,
                'name' => $course->course_description,
                'code' => $course->course_code,
                'is_ge_program' => $geCourseId !== null && (int) $course->id === (int) $geCourseId,
            ];
            });

        Log::info('Department courses fetched', [
            'department_id' => $id,
            'department_name' => $department->department_description,
            'courses_count' => $courses->count(),
            'courses' => $courses->toArray()
        ]);

        return $courses;
    } catch (\Exception $e) {
        Log::error('Error fetching department courses', [
            'department_id' => $id,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'error' => 'Failed to fetch courses',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/check-duplicate-name', function (Request $request) {
    $firstName = $request->query('first_name');
    $lastName = $request->query('last_name');
    $email = $request->query('email') . '@brokenshire.edu.ph';
    
    $exists = User::where(function($query) use ($firstName, $lastName, $email) {
        $query->where(function($q) use ($firstName, $lastName) {
            $q->where('first_name', $firstName)
              ->where('last_name', $lastName);
        })->orWhere('email', $email);
    })->exists();
    
    return response()->json(['exists' => $exists]);
});
