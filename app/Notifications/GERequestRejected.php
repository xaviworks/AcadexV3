<?php

namespace App\Notifications;

use App\Models\GESubjectRequest;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to Chairperson when a GE assignment request is rejected.
 * Supports both database and email channels.
 * 
 * Admin view: Full rejection details with IDs
 * User view: Friendly notification about GE request rejection
 */
class GERequestRejected extends BaseNotification
{
    public function __construct(
        protected GESubjectRequest $request,
        protected User $instructor,
        protected User $requestedBy,
        protected ?User $rejectedBy = null
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
        return 'bi-x-circle-fill';
    }

    public function getColor(): string
    {
        return 'danger';
    }

    public function getAdminMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        $rejectorName = $this->rejectedBy?->full_name ?? 'GE Coordinator';
        
        return "[GE Request Rejected] The GE assignment request for {$instructorName} (ID: {$this->instructor->id}) has been rejected by {$rejectorName}.";
    }

    public function getUserMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        
        return "Your GE assignment request for {$instructorName} has been rejected";
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
            'rejected_by_id' => $this->rejectedBy?->id,
            'rejected_by_name' => $this->rejectedBy?->full_name,
            'rejected_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $instructorName = $this->instructor->full_name;
        
        return (new MailMessage)
            ->subject('GE Assignment Request Update - Acadex')
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your GE teaching assignment request for **{$instructorName}** has been reviewed.")
            ->line("Unfortunately, the request was not approved at this time.")
            ->line("If you have questions about this decision, please contact the GE Coordinator.")
            ->action('View Instructors', $this->getActionUrl())
            ->salutation('Best regards, The Acadex Team');
    }
}
