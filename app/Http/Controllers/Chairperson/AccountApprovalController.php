<?php

namespace App\Http\Controllers\Chairperson;

use App\Http\Controllers\Controller;
use App\Models\UnverifiedUser;
use App\Models\User;
use App\Support\Organization\GEContext;
use App\Listeners\NotifyUserCreated;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountApprovalController extends Controller
{
    /**
     * Display a list of all pending instructor accounts for approval.
     */
    public function index(): View
    {
        Gate::authorize('chairperson');

        $instructorsQuery = User::where('role', 0)
            ->where('department_id', Auth::user()->department_id)
            ->where('course_id', Auth::user()->course_id);

        GEContext::applyNonGERegistrationTargetFilter($instructorsQuery);
        $instructors = $instructorsQuery
            ->orderBy('is_active', 'desc')
            ->orderBy('last_name')
            ->get();

        $geRequests = \App\Models\GESubjectRequest::whereIn('instructor_id', $instructors->pluck('id'))
            ->get()
            ->keyBy('instructor_id');

        // Eager-load related department and course for display, filtered by chairperson's department and course
        // Exclude GE department instructors
        // Only show verified email accounts
        $pendingAccountsQuery = UnverifiedUser::with(['department', 'course'])
            ->where('department_id', Auth::user()->department_id)
            ->where('course_id', Auth::user()->course_id)
            ->whereNotNull('email_verified_at');

        GEContext::applyNonGERegistrationTargetFilter($pendingAccountsQuery);
        $pendingAccounts = $pendingAccountsQuery->get();

        return view('chairperson.manage-instructors', compact('instructors', 'pendingAccounts', 'geRequests'));
    }

    /**
     * Approve a pending instructor and migrate their data to the main users table.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function approve(int $id): RedirectResponse
    {
        Gate::authorize('chairperson');

        $pending = UnverifiedUser::where('id', $id)
            ->where('department_id', Auth::user()->department_id)
            ->where('course_id', Auth::user()->course_id)
            ->whereNotNull('email_verified_at')
            ;

        GEContext::applyNonGERegistrationTargetFilter($pending);
        $pending = $pending->first();

        if (!$pending) {
            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }

        try {
            // Transfer to the main users table
            $newUser = User::create([
                'first_name'    => $pending->first_name,
                'middle_name'   => $pending->middle_name,
                'last_name'     => $pending->last_name,
                'email'         => $pending->email,
                'password'      => $pending->password, // Already hashed
                'department_id' => $pending->department_id,
                'course_id'     => $pending->course_id,
                'role'          => 0, // Instructor role
                'is_active'     => true,
            ]);

            // Notify admins about new user creation
            NotifyUserCreated::handle($newUser, Auth::user());
            
            // Notify the instructor that their account was approved (Email + System)
            NotificationService::notifyInstructorApproved($newUser, Auth::user());

            // Remove from unverified list
            $pending->delete();

            return back()->with('success', 'Instructor account has been approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to approve account: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject and delete a pending instructor account request.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function reject(int $id): RedirectResponse
    {
        Gate::authorize('chairperson');

        $pending = UnverifiedUser::where('id', $id)
            ->where('department_id', Auth::user()->department_id)
            ->where('course_id', Auth::user()->course_id)
            ;

        GEContext::applyNonGERegistrationTargetFilter($pending);
        $pending = $pending->first();

        if (!$pending) {
            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }
        
        // Store info before deletion for notification
        $email = $pending->email;
        $name = trim($pending->first_name . ' ' . $pending->last_name);
        
        // Send rejection email notification to the instructor
        NotificationService::notifyInstructorRejected($email, $name, Auth::user());
            
        $pending->delete();

        return back()->with('success', 'Instructor account request has been rejected and removed.');
    }
}
