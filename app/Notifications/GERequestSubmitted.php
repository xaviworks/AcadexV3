<?php

namespace App\Notifications;

use App\Models\GESubjectRequest;
use App\Models\User;

/**
 * Notification sent to GE Coordinator when a new GE assignment request is submitted.
 * 
 * Admin view: Full request details with IDs
 * User view: Friendly notification about new GE request
 */
class GERequestSubmitted extends BaseNotification
{
    public function __construct(
        protected GESubjectRequest $request,
        protected User $instructor,
        protected User $requestedBy
    ) {}

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
        return 'bi-person-badge';
    }

    public function getColor(): string
    {
        return 'info';
    }

    public function getAdminMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        $requestedByName = $this->requestedBy->full_name;
        $departmentName = $this->instructor->department?->department_name ?? 'Unknown';
        $courseName = $this->instructor->course?->course_code ?? 'Unknown';
        
        return "[GE Request] {$requestedByName} (ID: {$this->requestedBy->id}, Chairperson) submitted a GE assignment request for {$instructorName} (ID: {$this->instructor->id}). Department: {$departmentName}, Course: {$courseName}";
    }

    public function getUserMessage(): string
    {
        $instructorName = $this->instructor->full_name;
        $requestedByName = $this->requestedBy->full_name;
        
        return "{$requestedByName} requested GE teaching access for {$instructorName}";
    }

    public function getActionUrl(): ?string
    {
        return route('gecoordinator.instructors');
    }

    public function getActionText(): ?string
    {
        return 'Review GE Requests';
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
            'department_id' => $this->instructor->department_id,
            'department_name' => $this->instructor->department?->department_name,
            'course_id' => $this->instructor->course_id,
            'course_code' => $this->instructor->course?->course_code,
        ];
    }
}
