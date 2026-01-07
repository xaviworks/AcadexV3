<?php

namespace App\Services\Notifications;

use App\Models\User;

/**
 * Trait for notification management utilities.
 * Handles fetching, marking read/viewed, and cleanup operations.
 */
trait ManagesNotifications
{
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

        if ($category) {
            $query->where('data->category', $category);
        }

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        if ($cursor) {
            $query->where('created_at', '<', $cursor);
        }

        $notifications = $query
            ->orderBy('created_at', 'desc')
            ->limit($limit + 1)
            ->get();

        $hasMore = $notifications->count() > $limit;
        if ($hasMore) {
            $notifications = $notifications->take($limit);
        }

        return [
            'notifications' => $notifications,
            'has_more' => $hasMore,
            'next_cursor' => $hasMore && $notifications->isNotEmpty()
                ? $notifications->last()->created_at->toIso8601String()
                : null,
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
     * Get unviewed notification count for a user (for badge display).
     */
    public static function getUnviewedCount(User $user): int
    {
        return $user->notifications()->whereNull('viewed_at')->count();
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
            ->whereNotNull('read_at')
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
            'extra' => $isAdmin ? array_diff_key($data, array_flip([
                'type', 'category', 'priority', 'icon', 'color',
                'admin_message', 'user_message', 'action_url', 'action_text'
            ])) : null,
        ];

        if ($category === 'announcement') {
            $response['announcement_title'] = $data['title'] ?? null;
            $response['announcement_content'] = $data['full_message'] ?? null;
            $response['announcement_sender'] = $data['actor_name'] ?? null;
        }

        return $response;
    }
}
