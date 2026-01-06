<?php

namespace App\Notifications;

use App\Models\User;

/**
 * Notification sent to Instructors with important updates from Chairperson/Dean.
 * 
 * Admin view: Full details with sender info and context
 * User view: Clear announcement with action if applicable
 */
class InstructorAnnouncement extends BaseNotification
{
    public function __construct(
        protected User $sender,
        protected string $title,
        protected string $message,
        protected string $announcementPriority = 'normal',
        protected ?string $actionUrl = null,
        protected ?string $actionText = null
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
        $roleLabel = match($this->sender->role) {
            1 => 'Chairperson',
            2 => 'Dean',
            3 => 'Admin',
            5 => 'VPAA',
            default => 'User',
        };
        
        return "[Announcement - {$this->announcementPriority}] From {$senderName} ({$roleLabel}, ID: {$this->sender->id}): {$this->title} - {$this->message}";
    }

    public function getUserMessage(): string
    {
        $senderName = trim($this->sender->first_name . ' ' . $this->sender->last_name);
        $roleLabel = match($this->sender->role) {
            1 => 'Chairperson',
            2 => 'Dean',
            5 => 'VPAA',
            default => '',
        };
        
        $prefix = $roleLabel ? "{$roleLabel} {$senderName}" : $senderName;
        
        return "{$prefix}: {$this->title}";
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
            'actor_id' => $this->sender->id,
            'actor_name' => $this->sender->full_name,
            'actor_role' => $this->sender->role,
            'title' => $this->title,
            'full_message' => $this->message,
        ];
    }
}
