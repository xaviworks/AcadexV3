<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Trait for system-wide notifications.
 * Handles announcements, maintenance alerts, and broadcast messages.
 */
trait SendsSystemNotifications
{
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
}
