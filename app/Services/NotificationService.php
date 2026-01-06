<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subject;
use App\Notifications\GradeSubmitted;
use App\Notifications\SubjectAssigned;
use App\Notifications\InstructorAnnouncement;
use App\Notifications\SecurityAlert;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;

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
     * Send an announcement to instructors.
     */
    public static function sendInstructorAnnouncement(
        Collection|array $instructors,
        string $title,
        string $message,
        string $priority = 'normal',
        ?string $actionUrl = null,
        ?string $actionText = null
    ): void {
        $sender = Auth::user();
        if (!$sender) {
            return;
        }

        $instructors = $instructors instanceof Collection ? $instructors : collect($instructors);

        Notification::send(
            $instructors,
            new InstructorAnnouncement($sender, $title, $message, $priority, $actionUrl, $actionText)
        );
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
     */
    public static function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
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
}
