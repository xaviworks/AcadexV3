<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Subject;

/**
 * Notification sent to Chairpersons when an instructor submits grades.
 * 
 * Admin view: Full technical details with IDs and metadata
 * User view: Friendly message with clear action
 */
class GradeSubmitted extends BaseNotification
{
    public function __construct(
        protected User $instructor,
        protected Subject $subject,
        protected string $term,
        protected int $studentsGraded
    ) {}

    public function getCategory(): string
    {
        return 'academic';
    }

    public function getIcon(): string
    {
        return 'bi-journal-check';
    }

    public function getColor(): string
    {
        return 'success';
    }

    public function getAdminMessage(): string
    {
        $instructorName = trim($this->instructor->first_name . ' ' . $this->instructor->last_name);
        $subjectInfo = "{$this->subject->subject_code} - {$this->subject->subject_description}";
        $termLabel = ucfirst($this->term);
        
        return "[Grade Entry] {$instructorName} (ID: {$this->instructor->id}) submitted {$termLabel} grades for {$this->studentsGraded} student(s) in {$subjectInfo} (Subject ID: {$this->subject->id}, Course: {$this->subject->course_id})";
    }

    public function getUserMessage(): string
    {
        $instructorName = trim($this->instructor->first_name . ' ' . $this->instructor->last_name);
        $termLabel = ucfirst($this->term);
        
        return "{$instructorName} submitted {$termLabel} grades for {$this->studentsGraded} student(s) in {$this->subject->subject_code}";
    }

    public function getActionUrl(): ?string
    {
        return route('chairperson.viewGrades');
    }

    public function getActionText(): ?string
    {
        return 'View Grades';
    }

    public function getExtraData(): array
    {
        return [
            'actor_id' => $this->instructor->id,
            'actor_name' => $this->instructor->full_name,
            'subject_id' => $this->subject->id,
            'subject_code' => $this->subject->subject_code,
            'subject_description' => $this->subject->subject_description,
            'course_id' => $this->subject->course_id,
            'term' => $this->term,
            'students_graded' => $this->studentsGraded,
        ];
    }
}
