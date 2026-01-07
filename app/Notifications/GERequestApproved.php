<?php

namespace App\Notifications;

use App\Models\GESubjectRequest;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to Chairperson when a GE assignment request is approved.
 * Supports both database and email channels.
 * 
 * Admin view: Full approval details with IDs
 * User view: Friendly notification about GE request approval
 */
class GERequestApproved extends BaseNotification
{
    public function __construct(
        protected GESubjectRequest $request,
        protected User $instructor,
        protected User $requestedBy,
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
        return 'normal';
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
        $approverName = $this->approvedBy?->full_name ?? 'GE Coordinator';
        
        return "[GE Request Approved] The GE assignment request for {$instructorName} (ID: {$this->instructor->id}) has been approved by {$approverName}. Instructor can now teach GE subjects.";
    }

    public function getUserMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        
        return "Your GE assignment request for {$instructorName} has been approved";
    }

    public function getActionUrl(): ?string
    {
        return route('chairperson.instructors');
    }

    public function getActionText(): ?string
    {
        return 'View Instructors';
    }

    public function getExtraData(): array
    {
        return [
            'request_id' => $this->request->id,
            'instructor_id' => $this->instructor->id,
            'instructor_name' => $this->instructor->full_name,
            'instructor_email' => $this->instructor->email,
            'requested_by_id' => $this->requestedBy->id,
            'requested_by_name' => $this->requestedBy->full_name,
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
        $instructorName = $this->instructor->full_name;
        
        return (new MailMessage)
            ->subject('GE Assignment Request Approved - Acadex')
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("Great news! Your GE teaching assignment request for **{$instructorName}** has been approved.")
            ->line("{$instructorName} can now be assigned to teach General Education subjects.")
            ->action('View Instructors', $this->getActionUrl())
            ->salutation('Best regards, The Acadex Team');
    }
}
