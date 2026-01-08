<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Base notification class with role-aware message formatting.
 * Admin users receive detailed technical information.
 * End users receive friendly, actionable messages.
 * 
 * Note: Notifications are processed synchronously for immediate delivery.
 * To enable queued notifications, implement ShouldQueue and use Queueable trait.
 */
abstract class BaseNotification extends Notification
{

    /**
     * Get the notification category (academic, security, system, etc.)
     */
    abstract public function getCategory(): string;

    /**
     * Get the notification priority (low, normal, high, urgent)
     */
    public function getPriority(): string
    {
        return 'normal';
    }

    /**
     * Get the icon class for this notification type
     */
    abstract public function getIcon(): string;

    /**
     * Get the color theme for this notification (success, warning, danger, info, primary)
     */
    abstract public function getColor(): string;

    /**
     * Get the detailed message for admin users.
     * Should include technical details, IDs, timestamps, etc.
     */
    abstract public function getAdminMessage(): string;

    /**
     * Get the friendly message for end users.
     * Should be concise, clear, and actionable.
     */
    abstract public function getUserMessage(): string;

    /**
     * Get the action URL if applicable
     */
    public function getActionUrl(): ?string
    {
        return null;
    }

    /**
     * Get the action button text if applicable
     */
    public function getActionText(): ?string
    {
        return null;
    }

    /**
     * Get extra data for the notification
     */
    public function getExtraData(): array
    {
        return [];
    }

    /**
     * Get the channels the notification should be sent to.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check user preferences
        $prefs = $notifiable->notificationPreferences;
        
        if ($prefs?->push_enabled ?? true) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => class_basename(static::class),
            'category' => $this->getCategory(),
            'priority' => $this->getPriority(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'admin_message' => $this->getAdminMessage(),
            'user_message' => $this->getUserMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
        ], $this->getExtraData());
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => class_basename(static::class),
            'category' => $this->getCategory(),
            'priority' => $this->getPriority(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'message' => $notifiable->isAdmin() 
                ? $this->getAdminMessage() 
                : $this->getUserMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'notification.received';
    }
}
