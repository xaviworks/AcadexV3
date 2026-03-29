<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GECoordinatorSubjectManagementCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_subject_instructors_allows_ge_department_managed_subjects_even_when_course_is_not_legacy_id(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $geDepartment = Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'General Education',
            'is_deleted' => false,
        ]);

        $programDepartment = Department::create([
            'department_code' => 'TCSDEP',
            'department_description' => 'Computer Studies Department',
            'is_deleted' => false,
        ]);

        // Ensure the target subject does not use the legacy course_id=1 assumption.
        Course::create([
            'course_code' => 'TDUMMY',
            'course_description' => 'Dummy Course',
            'department_id' => $programDepartment->id,
            'is_deleted' => false,
        ]);

        $programCourse = Course::create([
            'course_code' => 'TBSCS',
            'course_description' => 'BS Computer Science',
            'department_id' => $programDepartment->id,
            'is_deleted' => false,
        ]);

        /** @var User $coordinator */
        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $geDepartment->id,
            'is_active' => true,
        ]);

        /** @var User $instructor */
        $instructor = User::factory()->create([
            'role' => 0,
            'department_id' => $programDepartment->id,
            'is_active' => true,
        ]);

        $subject = Subject::create([
            'subject_code' => 'GE-DEPT-ONLY',
            'subject_description' => 'GE Department Managed Subject',
            'department_id' => $geDepartment->id,
            'course_id' => $programCourse->id,
            'is_universal' => false,
            'is_deleted' => false,
        ]);

        $subject->instructors()->attach($instructor->id);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.getSubjectInstructors', ['subject' => $subject->id]));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $instructor->id,
        ]);
    }
}
