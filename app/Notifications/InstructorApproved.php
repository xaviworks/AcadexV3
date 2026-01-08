<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to Instructors when their account is approved.
 * Supports both database and email channels.
 * 
 * Admin view: Full approval details with IDs
 * User view: Welcoming message about account approval
 */
class InstructorApproved extends BaseNotification
{
    public function __construct(
        protected User $instructor,
        protected ?User $approvedBy = null
    ) {}

    /**
     * Get the channels the notification should be sent to.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check user preferences for email
        $prefs = $notifiable->notificationPreferences;
        
        if ($prefs?->email_enabled ?? true) {
            $channels[] = 'mail';
        }

        if ($prefs?->push_enabled ?? true) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function getCategory(): string
    {
        return 'academic';
    }

    public function getPriority(): string
    {
        return 'high';
    }

    public function getIcon(): string
    {
        return 'bi-check-circle-fill';
    }

    public function getColor(): string
    {
        return 'success';
    }

    public function getAdminMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        $approverName = $this->approvedBy?->full_name ?? 'System';
        $approverRole = $this->approvedBy ? match($this->approvedBy->role) {
            1 => 'Chairperson',
            4 => 'GE Coordinator',
            default => 'User',
        } : 'System';
        
        return "[Account Approved] Instructor {$instructorName} (ID: {$this->instructor->id}, Email: {$this->instructor->email}) was approved by {$approverName} ({$approverRole})";
    }

    public function getUserMessage(): string
    {
        return "Your instructor account has been approved. Welcome to Acadex!";
    }

    public function getActionUrl(): ?string
    {
        return route('instructor.dashboard');
    }

    public function getActionText(): ?string
    {
        return 'Go to Dashboard';
    }

    public function getExtraData(): array
    {
        return [
            'instructor_id' => $this->instructor->id,
            'instructor_email' => $this->instructor->email,
            'instructor_name' => $this->instructor->full_name,
            'approved_by_id' => $this->approvedBy?->id,
            'approved_by_name' => $this->approvedBy?->full_name,
            'approved_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Acadex Instructor Account Has Been Approved')
            ->greeting("Hello {$this->instructor->first_name}!")
            ->line('Great news! Your instructor account has been approved.')
            ->line('You can now access the Acadex platform and start managing your courses and students.')
            ->action('Go to Dashboard', $this->getActionUrl())
            ->line('Welcome to the Acadex academic management system!')
            ->salutation('Best regards, The Acadex Team');
    }
}
