<?php

namespace App\Notifications;

use App\Models\User;

/**
 * Notification sent by Admin as an announcement to selected users.
 * 
 * Admin view: Full details with target audience and metadata
 * User view: Clear announcement message with optional action
 */
class AdminAnnouncement extends BaseNotification
{
    public const TARGET_SPECIFIC_USER = 'specific_user';
    public const TARGET_DEPARTMENT = 'department';
    public const TARGET_PROGRAM = 'program';
    public const TARGET_ROLE = 'role';

    public function __construct(
        protected User $sender,
        protected string $title,
        protected string $message,
        protected string $targetType,
        protected ?string $targetName = null,
        protected string $announcementPriority = 'normal',
        protected ?string $actionUrl = null,
        protected ?string $actionText = null,
        protected array $metadata = []
    ) {}

    public function getCategory(): string
    {
        return 'announcement';
    }

    public function getPriority(): string
    {
        return $this->announcementPriority;
    }

    public function getIcon(): string
    {
        return match($this->announcementPriority) {
            'urgent' => 'bi-exclamation-triangle-fill',
            'high' => 'bi-megaphone-fill',
            default => 'bi-megaphone',
        };
    }

    public function getColor(): string
    {
        return match($this->announcementPriority) {
            'urgent' => 'danger',
            'high' => 'warning',
            default => 'info',
        };
    }

    public function getAdminMessage(): string
    {
        $senderName = trim($this->sender->first_name . ' ' . $this->sender->last_name);
        $targetLabel = match($this->targetType) {
            self::TARGET_SPECIFIC_USER => "Specific User ({$this->targetName})",
            self::TARGET_DEPARTMENT => "Department ({$this->targetName})",
            self::TARGET_PROGRAM => "Program ({$this->targetName})",
            self::TARGET_ROLE => "Role ({$this->targetName})",
            default => 'Unknown Target',
        };
        
        $recipientCount = $this->metadata['recipient_count'] ?? '?';
        
        return "[Admin Announcement - {$this->announcementPriority}] From {$senderName} (Admin, ID: {$this->sender->id}) to {$targetLabel} ({$recipientCount} recipients): {$this->title} - {$this->message}";
    }

    public function getUserMessage(): string
    {
        return "📢 Admin Announcement: {$this->title}";
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
        return array_merge([
            'actor_id' => $this->sender->id,
            'actor_name' => $this->sender->full_name,
            'actor_role' => $this->sender->role,
            'title' => $this->title,
            'full_message' => $this->message,
            'target_type' => $this->targetType,
            'target_name' => $this->targetName,
        ], $this->metadata);
    }
}
