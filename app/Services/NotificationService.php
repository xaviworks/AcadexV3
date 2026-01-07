<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subject;
use App\Models\UnverifiedUser;
use App\Models\GESubjectRequest;
use App\Notifications\GradeSubmitted;
use App\Notifications\SubjectAssigned;
use App\Notifications\SecurityAlert;
use App\Notifications\SystemNotification;
use App\Notifications\InstructorPendingApproval;
use App\Notifications\InstructorApproved;
use App\Notifications\InstructorRejected;
use App\Notifications\GERequestSubmitted;
use App\Notifications\GERequestApproved;
use App\Notifications\GERequestRejected;
use App\Notifications\CourseAssigned;
use App\Notifications\CourseRemoved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Central service for managing notifications throughout the application.
 * Handles sending notifications to appropriate recipients based on roles and context.
 */
class NotificationService
{
    /**
     * Notify chairperson(s) when an instructor submits grades.
     */
    public static function notifyGradeSubmitted(
        Subject $subject,
        string $term,
        int $studentsGraded
    ): void {
        $instructor = Auth::user();
        if (!$instructor) {
            return;
        }

        $recipients = collect();

        // Notify chairperson of the course
        if ($subject->course_id) {
            $chairperson = User::where('role', 1) // Chairperson
                ->where('course_id', $subject->course_id)
                ->where('is_active', true)
                ->where('id', '!=', $instructor->id)
                ->first();

            if ($chairperson) {
                $recipients->push($chairperson);
            }
        }

        // Notify GE Coordinator if this is a GE subject (department_id = 1)
        if ($subject->department_id == 1) {
            $geCoordinators = User::where('role', 4) // GE Coordinator
                ->where('is_active', true)
                ->where('id', '!=', $instructor->id)
                ->whereNotIn('id', $recipients->pluck('id'))
                ->get();

            $recipients = $recipients->merge($geCoordinators);
        }

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new GradeSubmitted($instructor, $subject, $term, $studentsGraded)
            );
        }
    }

    /**
     * Notify an instructor when they're assigned a new subject.
     */
    public static function notifySubjectAssigned(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $assignedBy = Auth::user();
        if (!$assignedBy) {
            return;
        }

        // Don't notify if assigning to self
        if ($assignedBy->id === $instructor->id) {
            return;
        }

        $instructor->notify(new SubjectAssigned($assignedBy, $subject, $academicPeriod));
    }

    /**
     * Send security alert to all admin users.
     */
    public static function notifySecurityAlert(
        string $alertType,
        ?User $affectedUser = null,
        ?User $actorUser = null,
        array $metadata = []
    ): void {
        // Add request metadata if not provided
        if (!isset($metadata['ip_address'])) {
            $metadata['ip_address'] = request()->ip();
        }
        if (!isset($metadata['user_agent'])) {
            $metadata['user_agent'] = request()->userAgent();
        }

        // Get all active admins
        $admins = User::where('role', 3)
            ->where('is_active', true)
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new SecurityAlert($alertType, $affectedUser, $actorUser, $metadata)
            );
        }
    }

    /**
     * Send system notification to specific users or all users.
     */
    public static function sendSystemNotification(
        string $type,
        string $title,
        string $message,
        Collection|array|null $recipients = null,
        string $priority = 'normal',
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $metadata = []
    ): void {
        // If no recipients specified, send to all active users
        if ($recipients === null) {
            $recipients = User::where('is_active', true)->get();
        } else {
            $recipients = $recipients instanceof Collection ? $recipients : collect($recipients);
        }

        Notification::send(
            $recipients,
            new SystemNotification($type, $title, $message, $priority, $actionUrl, $actionText, $metadata)
        );
    }

    /**
     * Get notifications for a user with cursor-based pagination (for infinite scroll).
     */
    public static function getNotifications(
        User $user,
        ?string $cursor = null,
        int $limit = 20,
        ?string $category = null,
        bool $unreadOnly = false
    ): array {
        $query = $user->notifications();

        // Filter by category if specified
        if ($category) {
            $query->where('data->category', $category);
        }

        // Filter unread only
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        // Cursor-based pagination
        if ($cursor) {
            $query->where('created_at', '<', $cursor);
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->limit($limit + 1) // Get one extra to check if there are more
            ->get();

        $hasMore = $notifications->count() > $limit;
        if ($hasMore) {
            $notifications = $notifications->take($limit);
        }

        $nextCursor = $hasMore && $notifications->isNotEmpty()
            ? $notifications->last()->created_at->toIso8601String()
            : null;

        return [
            'notifications' => $notifications,
            'has_more' => $hasMore,
            'next_cursor' => $nextCursor,
        ];
    }

    /**
     * Get unread notification count for a user.
     * (Uses read_at - notifications that haven't been clicked/interacted with)
     */
    public static function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Get unviewed notification count for a user (for badge display).
     * (Uses viewed_at - notifications that haven't been seen in dropdown)
     */
    public static function getUnviewedCount(User $user): int
    {
        return $user->notifications()
            ->whereNull('viewed_at')
            ->count();
    }

    /**
     * Mark a notification as read.
     */
    public static function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as viewed (seen in dropdown).
     * This clears the badge count but doesn't mark them as "read".
     */
    public static function markAllAsViewed(User $user): int
    {
        return $user->notifications()
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();
        return $count;
    }

    /**
     * Delete old notifications (for cleanup).
     */
    public static function pruneOldNotifications(int $daysOld = 90): int
    {
        return \Illuminate\Notifications\DatabaseNotification::where('created_at', '<', now()->subDays($daysOld))
            ->whereNotNull('read_at') // Only delete read notifications
            ->delete();
    }

    /**
     * Format notification for API response based on user role.
     */
    public static function formatForResponse(
        \Illuminate\Notifications\DatabaseNotification $notification,
        User $user
    ): array {
        $data = $notification->data;
        $isAdmin = $user->isAdmin();
        $category = $data['category'] ?? 'general';

        // Base response
        $response = [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'unknown',
            'category' => $category,
            'priority' => $data['priority'] ?? 'normal',
            'icon' => $data['icon'] ?? 'bi-bell',
            'color' => $data['color'] ?? 'info',
            'message' => $isAdmin 
                ? ($data['admin_message'] ?? $data['user_message'] ?? 'Notification')
                : ($data['user_message'] ?? 'Notification'),
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
            'is_read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
            'time_ago' => $notification->created_at->diffForHumans(),
            // Include extra data for admin
            'extra' => $isAdmin ? array_diff_key($data, array_flip([
                'type', 'category', 'priority', 'icon', 'color',
                'admin_message', 'user_message', 'action_url', 'action_text'
            ])) : null,
        ];

        // For announcements, include full_message and title for all users (so they can read it)
        if ($category === 'announcement') {
            $response['announcement_title'] = $data['title'] ?? null;
            $response['announcement_content'] = $data['full_message'] ?? null;
            $response['announcement_sender'] = $data['actor_name'] ?? null;
        }

        return $response;
    }

    // ============================
    // Instructor Account Notifications
    // ============================

    /**
     * Notify appropriate approver when a new instructor registers and is pending approval.
     * - GE Department instructors → notify GE Coordinators
     * - Other departments → notify Chairpersons of that department/course
     * System notification only (no email).
     */
    public static function notifyInstructorPending(UnverifiedUser $pendingUser): void
    {
        try {
            // Check if this is a GE department instructor
            $geDepartment = \App\Models\Department::where('department_code', 'GE')->first();
            $isGEDepartment = $geDepartment && $pendingUser->department_id === $geDepartment->id;
            
            if ($isGEDepartment) {
                // Notify GE Coordinators
                $recipients = User::where('role', 4) // GE Coordinator
                    ->where('is_active', true)
                    ->get();
                $recipientType = 'GE Coordinator';
            } else {
                // Notify Chairpersons of the same department and course
                $recipients = User::where('role', 1) // Chairperson
                    ->where('department_id', $pendingUser->department_id)
                    ->where('course_id', $pendingUser->course_id)
                    ->where('is_active', true)
                    ->get();
                $recipientType = 'Chairperson';
            }

            if ($recipients->isNotEmpty()) {
                Notification::send(
                    $recipients,
                    new InstructorPendingApproval($pendingUser)
                );
                
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
            // Create a temporary notifiable for sending email
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

    // ============================
    // GE Request Notifications
    // ============================

    /**
     * Notify GE Coordinator(s) when a new GE assignment request is submitted.
     * System notification only (no email).
     */
    public static function notifyGERequestSubmitted(GESubjectRequest $request): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            // Get all active GE Coordinators
            $geCoordinators = User::where('role', 4) // GE Coordinator
                ->where('is_active', true)
                ->get();

            if ($geCoordinators->isNotEmpty()) {
                Notification::send(
                    $geCoordinators,
                    new GERequestSubmitted($request, $instructor, $requestedBy)
                );
                
                Log::info('GE request submitted notification sent', [
                    'request_id' => $request->id,
                    'instructor_id' => $instructor->id,
                    'requested_by_id' => $requestedBy->id,
                    'coordinator_count' => $geCoordinators->count(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send GE request submitted notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    /**
     * Notify the requesting chairperson when a GE request is approved.
     * Email and system notification.
     */
    public static function notifyGERequestApproved(GESubjectRequest $request, ?User $approvedBy = null): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request approved notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            // Notify the chairperson who made the request
            $requestedBy->notify(new GERequestApproved($request, $instructor, $requestedBy, $approvedBy));
            
            Log::info('GE request approved notification sent', [
                'request_id' => $request->id,
                'instructor_id' => $instructor->id,
                'requested_by_id' => $requestedBy->id,
                'approved_by_id' => $approvedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send GE request approved notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    /**
     * Notify the requesting chairperson when a GE request is rejected.
     * Email and system notification.
     */
    public static function notifyGERequestRejected(GESubjectRequest $request, ?User $rejectedBy = null): void
    {
        try {
            $instructor = User::find($request->instructor_id);
            $requestedBy = User::find($request->requested_by);
            
            if (!$instructor || !$requestedBy) {
                Log::warning('GE request rejected notification skipped - missing user data', [
                    'request_id' => $request->id,
                ]);
                return;
            }
            
            // Notify the chairperson who made the request
            $requestedBy->notify(new GERequestRejected($request, $instructor, $requestedBy, $rejectedBy));
            
            Log::info('GE request rejected notification sent', [
                'request_id' => $request->id,
                'instructor_id' => $instructor->id,
                'requested_by_id' => $requestedBy->id,
                'rejected_by_id' => $rejectedBy?->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send GE request rejected notification', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);
        }
    }

    // ============================
    // Course Assignment Notifications
    // ============================

    /**
     * Notify an instructor when they're assigned to a course/subject.
     * Email and system notification.
     */
    public static function notifyCourseAssigned(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $assignedBy = Auth::user();
        if (!$assignedBy) {
            return;
        }

        // Don't notify if assigning to self
        if ($assignedBy->id === $instructor->id) {
            return;
        }

        try {
            $instructor->notify(new CourseAssigned($assignedBy, $subject, $academicPeriod));
            
            Log::info('Course assigned notification sent', [
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
                'assigned_by_id' => $assignedBy->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course assigned notification', [
                'error' => $e->getMessage(),
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
            ]);
        }
    }

    /**
     * Notify an instructor when they're removed from a course/subject.
     * Email and system notification.
     */
    public static function notifyCourseRemoved(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $removedBy = Auth::user();
        if (!$removedBy) {
            return;
        }

        // Don't notify if removing self
        if ($removedBy->id === $instructor->id) {
            return;
        }

        try {
            $instructor->notify(new CourseRemoved($removedBy, $subject, $academicPeriod));
            
            Log::info('Course removed notification sent', [
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
                'removed_by_id' => $removedBy->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course removed notification', [
                'error' => $e->getMessage(),
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
            ]);
        }
    }
}
