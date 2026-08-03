<?php

namespace App\Http\Controllers\GECoordinator;

use App\Http\Controllers\Controller;
use App\Listeners\NotifyUserCreated;
use App\Models\Department;
use App\Models\UnverifiedUser;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountApprovalController extends Controller
{
    /**
     * Display a list of all pending GE instructor accounts for approval.
     */
    public function index(): View
    {
        if (! Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Get GE department
        $geDepartment = Department::where('department_code', 'GE')->first();

        // Eager-load related department and course for display, filtered by GE department
        // Only show verified email accounts
        $pendingAccounts = UnverifiedUser::with(['department', 'course'])
            ->where('department_id', $geDepartment->id)
            ->whereNotNull('email_verified_at')
            ->get();

        return view('gecoordinator.manage-instructors', compact('pendingAccounts'));
    }

    /**
     * Approve a pending GE instructor and migrate their data to the main users table.
     */
    public function approve(Request $request, int $id): RedirectResponse|JsonResponse
    {
        if (! Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Get GE department
        $geDepartment = Department::where('department_code', 'GE')->first();

        if (! $geDepartment) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'GE Department not found.',
                ], 404);
            }

            return back()->withErrors(['error' => 'GE Department not found.']);
        }

        $pending = UnverifiedUser::where('id', $id)
            ->where('department_id', $geDepartment->id)
            ->whereNotNull('email_verified_at')
            ->first();

        if (! $pending) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pending account not found or already processed.',
                ], 404);
            }

            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }

        try {
            // Transfer to the main users table
            $newUser = User::create([
                'first_name' => $pending->first_name,
                'middle_name' => $pending->middle_name,
                'last_name' => $pending->last_name,
                'email' => $pending->email,
                'password' => $pending->password, // Already hashed
                'department_id' => $pending->department_id,
                'course_id' => $pending->course_id,
                'role' => 0, // Instructor role
                'is_active' => true,
            ]);

            // Remove from unverified list
            $pending->delete();

            // Notify admins about new user creation
            NotifyUserCreated::handle($newUser, Auth::user());

            // Notify the instructor that their account was approved (Email + System)
            NotificationService::notifyInstructorApproved($newUser, Auth::user());

            $message = 'GE Instructor account has been approved successfully.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $newUser,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to approve account: '.$e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to approve account: '.$e->getMessage()]);
        }
    }

    /**
     * Reject and delete a pending GE instructor account request.
     */
    public function reject(Request $request, int $id): RedirectResponse|JsonResponse
    {
        if (! Auth::user()->isGECoordinator()) {
            abort(403);
        }

        // Get GE department
        $geDepartment = Department::where('department_code', 'GE')->first();

        if (! $geDepartment) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'GE Department not found.',
                ], 404);
            }

            return back()->withErrors(['error' => 'GE Department not found.']);
        }

        $pending = UnverifiedUser::where('id', $id)
            ->where('department_id', $geDepartment->id)
            ->first();

        if (! $pending) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pending account not found or already processed.',
                ], 404);
            }

            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }

        // Store info before deletion for notification
        $email = $pending->email;
        $name = trim($pending->first_name.' '.$pending->last_name);

        // Send rejection email notification to the instructor
        NotificationService::notifyInstructorRejected($email, $name, Auth::user());

        $pending->delete();

        $message = 'GE Instructor account request has been rejected and removed.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
