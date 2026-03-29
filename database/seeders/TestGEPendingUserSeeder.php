<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Department;
use App\Models\UnverifiedUser;
use App\Support\Organization\GEContext;
use Illuminate\Database\Seeder;

class TestGEPendingUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure baseline departments exist for canonical GE registration routing.
        Department::firstOrCreate(
            ['department_code' => 'ASE'],
            [
                'department_description' => 'School of Arts and Science and Education',
                'is_deleted' => false,
            ]
        );

        Department::firstOrCreate(
            ['department_code' => 'GE'],
            [
                'department_description' => 'School of General Education',
                'is_deleted' => false,
            ]
        );

        $registrationDepartmentId = GEContext::geRegistrationDepartmentId();

        if ($registrationDepartmentId === null) {
            $this->command?->error('Unable to resolve GE registration department.');
            return;
        }

        $geCourseId = GEContext::geCourseId();

        if ($geCourseId === null) {
            $geCourse = Course::firstOrCreate(
                ['course_code' => 'GE'],
                [
                    'course_description' => 'General Education',
                    'department_id' => $registrationDepartmentId,
                    'is_deleted' => false,
                ]
            );

            $geCourseId = (int) $geCourse->id;
            GEContext::forgetResolvedIds();
        }

        UnverifiedUser::updateOrCreate(
            ['email' => 'testge@brokenshire.edu.ph'],
            [
                'first_name' => 'testge',
                'middle_name' => null,
                'last_name' => 'Pending',
                'password' => 'password',
                'department_id' => $registrationDepartmentId,
                'course_id' => $geCourseId,
                // Set as verified so it appears in pending approval queues immediately.
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info('GE pending test user created/updated successfully.');
        $this->command?->line('Username: testge (stored as first_name)');
        $this->command?->line('Email: testge@brokenshire.edu.ph');
        $this->command?->line('Password: password');
    }
}
