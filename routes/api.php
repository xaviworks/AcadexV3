<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Models\User;

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
        $department = Department::with(['courses' => function($query) {
            $query->where('is_deleted', false);
        }])->findOrFail($id);
        
        $courses = $department->courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->course_description,
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

/*
|--------------------------------------------------------------------------
| Tutorial API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\API\TutorialController;

Route::prefix('tutorials')->group(function () {
    Route::get('{role}', [TutorialController::class, 'getTutorialsByRole']);
    Route::get('{role}/{pageId}', [TutorialController::class, 'getTutorialByPage']);
    Route::get('statistics/all', [TutorialController::class, 'getStatistics']);
});
