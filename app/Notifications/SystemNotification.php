<?php

namespace App\Notifications;

use App\Models\User;

/**
 * Generic system notification for announcements, maintenance, updates, etc.
 * 
 * Admin view: Full system context
 * User view: Clear, actionable message
 */
class SystemNotification extends BaseNotification
{
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_UPDATE = 'update';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_REMINDER = 'reminder';
    public const TYPE_INFO = 'info';

    public function __construct(
        protected string $notificationType,
        protected string $title,
        protected string $message,
        protected string $notificationPriority = 'normal',
        protected ?string $actionUrl = null,
        protected ?string $actionText = null,
        protected array $metadata = []
    ) {}

    public function getCategory(): string
    {
        return 'system';
    }

    public function getPriority(): string
    {
        return $this->notificationPriority;
    }

    public function getIcon(): string
    {
        return match($this->notificationType) {
            self::TYPE_MAINTENANCE => 'bi-tools',
            self::TYPE_UPDATE => 'bi-arrow-up-circle-fill',
            self::TYPE_ANNOUNCEMENT => 'bi-broadcast',
            self::TYPE_REMINDER => 'bi-alarm',
            default => 'bi-info-circle-fill',
        };
    }

    public function getColor(): string
    {
        return match($this->notificationType) {
            self::TYPE_MAINTENANCE => 'warning',
            self::TYPE_UPDATE => 'info',
            self::TYPE_ANNOUNCEMENT => 'primary',
            self::TYPE_REMINDER => 'secondary',
            default => 'info',
        };
    }

    public function getAdminMessage(): string
    {
        $typeLabel = strtoupper($this->notificationType);
        $metadata = !empty($this->metadata) ? ' | Metadata: ' . json_encode($this->metadata) : '';
        
        return "[SYSTEM - {$typeLabel}] {$this->title}: {$this->message}{$metadata}";
    }

    public function getUserMessage(): string
    {
        return $this->title;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    public function getExtraData(): array
    {
        return [
            'notification_type' => $this->notificationType,
            'title' => $this->title,
            'full_message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
