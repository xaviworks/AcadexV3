<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Subject;

/**
 * Notification sent to Instructors when they receive a new subject assignment.
 * 
 * Admin view: Full assignment details with IDs
 * User view: Welcoming message about the new teaching assignment
 */
class SubjectAssigned extends BaseNotification
{
    public function __construct(
        protected User $assignedBy,
        protected Subject $subject,
        protected ?string $academicPeriod = null
    ) {}

    public function getCategory(): string
    {
        return 'academic';
    }

    public function getIcon(): string
    {
        return 'bi-book';
    }

    public function getColor(): string
    {
        return 'primary';
    }

    public function getAdminMessage(): string
    {
        $assignerName = trim($this->assignedBy->first_name . ' ' . $this->assignedBy->last_name);
        $subjectInfo = "{$this->subject->subject_code} - {$this->subject->subject_description}";
        $period = $this->academicPeriod ?? 'current period';
        
        return "[Subject Assignment] {$assignerName} (ID: {$this->assignedBy->id}, Role: {$this->assignedBy->role}) assigned subject {$subjectInfo} (ID: {$this->subject->id}) for {$period}";
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
}
