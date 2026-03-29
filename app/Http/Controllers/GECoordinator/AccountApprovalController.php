<?php

namespace App\Http\Controllers\GECoordinator;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\UnverifiedUser;
use App\Models\User;
use App\Support\Organization\GEContext;
use App\Listeners\NotifyUserCreated;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountApprovalController extends Controller
{
    private const LEGACY_GE_PAYLOAD_ERROR = 'Legacy GE-department registration payloads are no longer eligible. Use ASE department with the GE program selection.';

    /**
     * Display a list of all pending GE instructor accounts for approval.
     */
    public function index(): View
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $geDepartment = Department::generalEducation();
        $geCourseId = GEContext::geCourseId();

        $instructors = User::where('role', 0)
            ->where(function ($query) use ($geDepartment, $geCourseId) {
                if ($geDepartment) {
                    $query->where('department_id', $geDepartment->id)
                        ->orWhere('can_teach_ge', true);
                } else {
                    $query->where('can_teach_ge', true);
                }

                if ($geCourseId !== null) {
                    $query->orWhere('course_id', $geCourseId);
                }

                $query->orWhere(function ($subQuery) {
                    $subQuery->where('is_active', false)
                        ->whereHas('geSubjectRequests', function ($requestQuery) {
                            $requestQuery->where('status', 'approved');
                        });
                });
            })
            ->orderBy('last_name')
            ->get();

        // Eager-load related department and course for display.
        // Canonical GE cutover: include GE-program registrations only.
        $pendingAccountsQuery = UnverifiedUser::with(['department', 'course'])
            ->whereNotNull('email_verified_at');

        GEContext::applyGERegistrationTargetFilter($pendingAccountsQuery);
        $pendingAccounts = $pendingAccountsQuery->get();

        return view('gecoordinator.manage-instructors', compact('instructors', 'pendingAccounts'));
    }

    /**
     * Approve a pending GE instructor and migrate their data to the main users table.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function approve(int $id): RedirectResponse
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $pending = UnverifiedUser::find($id);

        if (!$pending) {
            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }

        if (!GEContext::isGERegistrationTarget((int) $pending->department_id, (int) $pending->course_id)) {
            return back()->withErrors(['error' => self::LEGACY_GE_PAYLOAD_ERROR]);
        }

        if (!$pending->email_verified_at) {
            return back()->withErrors(['error' => 'Pending account email is not yet verified.']);
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
                'can_teach_ge'  => true,
            ]);

            // Remove from unverified list
            $pending->delete();
            
            // Notify admins about new user creation
            NotifyUserCreated::handle($newUser, Auth::user());
            
            // Notify the instructor that their account was approved (Email + System)
            NotificationService::notifyInstructorApproved($newUser, Auth::user());

            return back()->with('success', 'GE Instructor account has been approved successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to approve account: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject and delete a pending GE instructor account request.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function reject(int $id): RedirectResponse
    {
        if (!Auth::user()->isGECoordinator()) {
            abort(403);
        }

        $pending = UnverifiedUser::find($id);

        if (!$pending) {
            return back()->withErrors(['error' => 'Pending account not found or already processed.']);
        }

        if (!GEContext::isGERegistrationTarget((int) $pending->department_id, (int) $pending->course_id)) {
            return back()->withErrors(['error' => self::LEGACY_GE_PAYLOAD_ERROR]);
        }
        
        // Store info before deletion for notification
        $email = $pending->email;
        $name = trim($pending->first_name . ' ' . $pending->last_name);
        
        // Send rejection email notification to the instructor
        NotificationService::notifyInstructorRejected($email, $name, Auth::user());
            
        $pending->delete();

        return back()->with('success', 'GE Instructor account request has been rejected and removed.');
    }
} 