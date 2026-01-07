<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\Subject;
use App\Notifications\GradeSubmitted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Trait for grade-related notifications.
 * Handles grade submission notifications to chairpersons and coordinators.
 */
trait SendsGradeNotifications
{
    /**
     * Notify chairperson(s) when an instructor submits grades.
     */
    public static function notifyGradeSubmitted(
        Subject $subject,
        string $term,
        int $studentsGraded
    ): void {
        $instructor = Auth::user();
        if (!$instructor) {
            return;
        }

        $recipients = collect();

        // Notify chairperson of the course
        if ($subject->course_id) {
            $chairperson = User::where('role', 1)
                ->where('course_id', $subject->course_id)
                ->where('is_active', true)
                ->where('id', '!=', $instructor->id)
                ->first();

            if ($chairperson) {
                $recipients->push($chairperson);
            }
        }

        // Notify GE Coordinator if this is a GE subject (department_id = 1)
        if ($subject->department_id == 1) {
            $geCoordinators = User::where('role', 4)
                ->where('is_active', true)
                ->where('id', '!=', $instructor->id)
                ->whereNotIn('id', $recipients->pluck('id'))
                ->get();

            $recipients = $recipients->merge($geCoordinators);
        }

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new GradeSubmitted($instructor, $subject, $term, $studentsGraded)
            );
        }
    }
}
