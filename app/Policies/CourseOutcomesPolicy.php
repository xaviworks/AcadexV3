<?php

namespace App\Policies;

use App\Models\CourseOutcomes;
use App\Models\Subject;
use App\Models\User;

class CourseOutcomesPolicy
{
    public function view(User $user, CourseOutcomes $courseOutcome): bool
    {
        return $this->canViewSubject($user, $courseOutcome->subject);
    }

    public function create(User $user, ?Subject $subject = null): bool
    {
        if (! in_array($user->role, [1, 4], true)) {
            return false;
        }

        return $subject === null || $this->canManageSubject($user, $subject);
    }

    public function update(User $user, CourseOutcomes $courseOutcome): bool
    {
        return $this->canManageSubject($user, $courseOutcome->subject);
    }

    public function delete(User $user, CourseOutcomes $courseOutcome): bool
    {
        return $this->canManageSubject($user, $courseOutcome->subject);
    }

    private function canViewSubject(User $user, ?Subject $subject): bool
    {
        if (! $subject) {
            return false;
        }

        if ($user->role === 0) {
            return $subject->instructor_id === $user->id
                || $subject->instructors()->whereKey($user->id)->exists();
        }

        return $this->canManageSubject($user, $subject);
    }

    private function canManageSubject(User $user, ?Subject $subject): bool
    {
        if (! $subject) {
            return false;
        }

        if ($user->role === 1) {
            return $user->course_id !== null
                && (int) $subject->course_id === (int) $user->course_id
                && ! $subject->is_universal;
        }

        return $user->role === 4 && $subject->is_universal;
    }
}
