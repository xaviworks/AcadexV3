<?php

namespace App\Notifications;

use App\Models\UnverifiedUser;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to rejected instructor applicants via email.
 * This notification is sent to the UnverifiedUser's email before their record is deleted.
 * 
 * Note: This extends base Notification since it doesn't need database storage
 * (the user record is being deleted).
 */
class InstructorRejected extends Notification
{
    public function __construct(
        protected string $instructorEmail,
        protected string $instructorName,
        protected ?User $rejectedBy = null
    ) {}

    /**
     * Get the channels the notification should be sent to.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $rejectorRole = $this->rejectedBy ? match($this->rejectedBy->role) {
            1 => 'Department Chairperson',
            4 => 'GE Coordinator',
            default => 'Administrator',
        } : 'Administrator';

        return (new MailMessage)
            ->subject('Acadex Instructor Account Application Update')
            ->greeting("Hello {$this->instructorName},")
            ->line('We regret to inform you that your instructor account application has not been approved at this time.')
            ->line("Your application was reviewed by the {$rejectorRole}.")
            ->line('If you believe this was in error or would like more information, please contact your department administrator.')
            ->line('Thank you for your interest in joining Acadex.')
            ->salutation('Best regards, The Acadex Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'instructor_email' => $this->instructorEmail,
            'instructor_name' => $this->instructorName,
            'rejected_by_id' => $this->rejectedBy?->id,
            'rejected_by_name' => $this->rejectedBy?->full_name,
            'rejected_at' => now()->toIso8601String(),
        ];
    }
}
