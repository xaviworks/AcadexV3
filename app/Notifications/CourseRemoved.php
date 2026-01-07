<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to Instructors when they are removed from a course/subject.
 * Supports both database and email channels.
 * 
 * Admin view: Full removal details with IDs
 * User view: Clear message about course removal
 */
class CourseRemoved extends BaseNotification
{
    public function __construct(
        protected User $removedBy,
        protected Subject $subject,
        protected ?string $academicPeriod = null
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
        return 'bi-book-half';
    }

    public function getColor(): string
    {
        return 'warning';
    }

    public function getAdminMessage(): string
    {
        $removerName = $this->removedBy->full_name;
        $removerRole = match($this->removedBy->role) {
            1 => 'Chairperson',
            4 => 'GE Coordinator',
            default => 'User',
        };
        $subjectInfo = "{$this->subject->subject_code} - {$this->subject->subject_description}";
        $period = $this->academicPeriod ?? 'current period';
        
        return "[Course Removal] {$removerName} ({$removerRole}, ID: {$this->removedBy->id}) removed assignment from subject {$subjectInfo} (ID: {$this->subject->id}) for {$period}";
    }

    public function getUserMessage(): string
    {
        $subjectCode = $this->subject->subject_code;
        $subjectName = $this->subject->subject_description;
        
        return "You have been removed from {$subjectCode} ({$subjectName})";
    }

    public function getActionUrl(): ?string
    {
        return route('instructor.dashboard');
    }

    public function getActionText(): ?string
    {
        return 'View Dashboard';
    }

    public function getExtraData(): array
    {
        return [
            'actor_id' => $this->removedBy->id,
            'actor_name' => $this->removedBy->full_name,
            'actor_role' => $this->removedBy->role,
            'subject_id' => $this->subject->id,
            'subject_code' => $this->subject->subject_code,
            'subject_description' => $this->subject->subject_description,
            'course_id' => $this->subject->course_id,
            'academic_period' => $this->academicPeriod,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subjectCode = $this->subject->subject_code;
        $subjectName = $this->subject->subject_description;
        $period = $this->academicPeriod ?? 'the current academic period';
        
        return (new MailMessage)
            ->subject("Course Assignment Update: {$subjectCode} - Acadex")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("You have been removed from a course assignment.")
            ->line("**Subject:** {$subjectCode} - {$subjectName}")
            ->line("**Period:** {$period}")
            ->line("If you have questions about this change, please contact your department chairperson.")
            ->action('View Dashboard', $this->getActionUrl())
            ->salutation('Best regards, The Acadex Team');
    }
}
