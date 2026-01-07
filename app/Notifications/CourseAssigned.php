<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to Instructors when they are assigned to a course/subject.
 * Supports both database and email channels.
 * 
 * Admin view: Full assignment details with IDs
 * User view: Welcoming message about the new teaching assignment
 */
class CourseAssigned extends BaseNotification
{
    public function __construct(
        protected User $assignedBy,
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
        return 'bi-book-fill';
    }

    public function getColor(): string
    {
        return 'primary';
    }

    public function getAdminMessage(): string
    {
        $assignerName = $this->assignedBy->full_name;
        $assignerRole = match($this->assignedBy->role) {
            1 => 'Chairperson',
            4 => 'GE Coordinator',
            default => 'User',
        };
        $subjectInfo = "{$this->subject->subject_code} - {$this->subject->subject_description}";
        $period = $this->academicPeriod ?? 'current period';
        
        return "[Course Assignment] {$assignerName} ({$assignerRole}, ID: {$this->assignedBy->id}) assigned subject {$subjectInfo} (ID: {$this->subject->id}) for {$period}";
    }

    public function getUserMessage(): string
    {
        $subjectCode = $this->subject->subject_code;
        $subjectName = $this->subject->subject_description;
        
        return "You've been assigned to teach {$subjectCode} ({$subjectName})";
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
            'actor_id' => $this->assignedBy->id,
            'actor_name' => $this->assignedBy->full_name,
            'actor_role' => $this->assignedBy->role,
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
            ->subject("New Course Assignment: {$subjectCode} - Acadex")
            ->greeting("Hello {$notifiable->first_name}!")
            ->line("You have been assigned to teach a new course.")
            ->line("**Subject:** {$subjectCode} - {$subjectName}")
            ->line("**Period:** {$period}")
            ->line("Please log in to the Acadex platform to view your students and manage your course.")
            ->action('Go to Dashboard', $this->getActionUrl())
            ->salutation('Best regards, The Acadex Team');
    }
}
