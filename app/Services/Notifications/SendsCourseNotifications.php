<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\Subject;
use App\Notifications\SubjectAssigned;
use App\Notifications\CourseAssigned;
use App\Notifications\CourseRemoved;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait for course/subject assignment notifications.
 * Handles course assigned and removed notifications to instructors.
 */
trait SendsCourseNotifications
{
    /**
     * Notify an instructor when they're assigned a new subject.
     * @deprecated Use notifyCourseAssigned() instead for email support.
     */
    public static function notifySubjectAssigned(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $assignedBy = Auth::user();
        if (!$assignedBy || $assignedBy->id === $instructor->id) {
            return;
        }

        $instructor->notify(new SubjectAssigned($assignedBy, $subject, $academicPeriod));
    }

    /**
     * Notify an instructor when they're assigned to a course/subject.
     * Email and system notification.
     */
    public static function notifyCourseAssigned(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $assignedBy = Auth::user();
        if (!$assignedBy || $assignedBy->id === $instructor->id) {
            return;
        }

        try {
            $instructor->notify(new CourseAssigned($assignedBy, $subject, $academicPeriod));
            
            Log::info('Course assigned notification sent', [
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
                'assigned_by_id' => $assignedBy->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course assigned notification', [
                'error' => $e->getMessage(),
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
            ]);
        }
    }

    /**
     * Notify an instructor when they're removed from a course/subject.
     * Email and system notification.
     */
    public static function notifyCourseRemoved(
        User $instructor,
        Subject $subject,
        ?string $academicPeriod = null
    ): void {
        $removedBy = Auth::user();
        if (!$removedBy || $removedBy->id === $instructor->id) {
            return;
        }

        try {
            $instructor->notify(new CourseRemoved($removedBy, $subject, $academicPeriod));
            
            Log::info('Course removed notification sent', [
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
                'removed_by_id' => $removedBy->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course removed notification', [
                'error' => $e->getMessage(),
                'instructor_id' => $instructor->id,
                'subject_id' => $subject->id,
            ]);
        }
    }
}
