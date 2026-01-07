<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\UnverifiedUser;
use App\Notifications\InstructorPendingApproval;
use App\Notifications\InstructorApproved;
use App\Notifications\InstructorRejected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Trait for instructor account-related notifications.
 * Handles pending, approved, and rejected instructor notifications.
 */
trait SendsInstructorNotifications
{
    /**
     * Notify appropriate approver when a new instructor registers and is pending approval.
     * - GE Department instructors → notify GE Coordinators
     * - Other departments → notify Chairpersons of that department/course
     * System notification only (no email).
     */
    public static function notifyInstructorPending(UnverifiedUser $pendingUser): void
    {
        try {
            $geDepartment = \App\Models\Department::where('department_code', 'GE')->first();
            $isGEDepartment = $geDepartment && $pendingUser->department_id === $geDepartment->id;
            
            if ($isGEDepartment) {
                $recipients = User::where('role', 4)->where('is_active', true)->get();
                $recipientType = 'GE Coordinator';
            } else {
                $recipients = User::where('role', 1)
                    ->where('department_id', $pendingUser->department_id)
                    ->where('course_id', $pendingUser->course_id)
                    ->where('is_active', true)
                    ->get();
                $recipientType = 'Chairperson';
            }

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new InstructorPendingApproval($pendingUser));
                
                Log::info('Pending instructor notification sent', [
                    'pending_user_id' => $pendingUser->id,
                    'pending_user_email' => $pendingUser->email,
                    'recipient_type' => $recipientType,
                    'recipient_count' => $recipients->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send pending instructor notification', [
                'error' => $e->getMessage(),
                'pending_user_id' => $pendingUser->id,
            ]);
        }
    }

    /**
     * Notify instructor when their account is approved.
     * Email and system notification.
     */
    public static function notifyInstructorApproved(User $instructor, ?User $approvedBy = null): void
    {
        try {
            $instructor->notify(new InstructorApproved($instructor, $approvedBy));
            
            Log::info('Instructor approved notification sent', [
                'instructor_id' => $instructor->id,
                'instructor_email' => $instructor->email,
                'approved_by_id' => $approvedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send instructor approved notification', [
                'error' => $e->getMessage(),
                'instructor_id' => $instructor->id,
            ]);
        }
    }

    /**
     * Notify instructor when their account is rejected.
     * Email notification only (account is being deleted).
     */
    public static function notifyInstructorRejected(
        string $email,
        string $name,
        ?User $rejectedBy = null
    ): void {
        try {
            $notifiable = new class($email) {
                public string $email;
                
                public function __construct(string $email) {
                    $this->email = $email;
                }
                
                public function routeNotificationForMail(): string {
                    return $this->email;
                }
            };
            
            $notifiable->notify(new InstructorRejected($email, $name, $rejectedBy));
            
            Log::info('Instructor rejected notification sent', [
                'instructor_email' => $email,
                'rejected_by_id' => $rejectedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send instructor rejected notification', [
                'error' => $e->getMessage(),
                'instructor_email' => $email,
            ]);
        }
    }
}
