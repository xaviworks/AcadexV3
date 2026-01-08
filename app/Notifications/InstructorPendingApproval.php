<?php

namespace App\Notifications;

use App\Models\UnverifiedUser;
use App\Models\Department;

/**
 * Notification sent to Chairpersons/GE Coordinators when a new instructor registers and is pending approval.
 * 
 * Admin view: Full registration details with IDs
 * User view: Friendly notification about new pending instructor
 */
class InstructorPendingApproval extends BaseNotification
{
    protected bool $isGEDepartment;
    
    public function __construct(
        protected UnverifiedUser $pendingUser
    ) {
        // Check if this is a GE department instructor
        $geDepartment = Department::where('department_code', 'GE')->first();
        $this->isGEDepartment = $geDepartment && $this->pendingUser->department_id === $geDepartment->id;
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
        return 'bi-person-plus';
    }

    public function getColor(): string
    {
        return 'warning';
    }

    public function getAdminMessage(): string
    {
        $fullName = trim($this->pendingUser->first_name . ' ' . ($this->pendingUser->middle_name ? $this->pendingUser->middle_name . ' ' : '') . $this->pendingUser->last_name);
        $departmentName = $this->pendingUser->department?->department_name ?? 'Unknown';
        $courseName = $this->pendingUser->course?->course_code ?? 'Unknown';
        
        return "[Pending Instructor] {$fullName} (Email: {$this->pendingUser->email}) has registered and is awaiting approval. Department: {$departmentName}, Course: {$courseName}";
    }

    public function getUserMessage(): string
    {
        $fullName = trim($this->pendingUser->first_name . ' ' . $this->pendingUser->last_name);
        
        return "New instructor {$fullName} is awaiting your approval";
    }

    public function getActionUrl(): ?string
    {
        // Route to appropriate management page based on department
        return $this->isGEDepartment 
            ? route('gecoordinator.instructors')
            : route('chairperson.instructors');
    }

    public function getActionText(): ?string
    {
        return 'Review Pending Instructors';
    }

    public function getExtraData(): array
    {
        return [
            'pending_user_id' => $this->pendingUser->id,
            'pending_user_email' => $this->pendingUser->email,
            'pending_user_name' => trim($this->pendingUser->first_name . ' ' . $this->pendingUser->last_name),
            'department_id' => $this->pendingUser->department_id,
            'department_name' => $this->pendingUser->department?->department_name,
            'course_id' => $this->pendingUser->course_id,
            'course_code' => $this->pendingUser->course?->course_code,
            'is_ge_department' => $this->isGEDepartment,
        ];
    }
}
