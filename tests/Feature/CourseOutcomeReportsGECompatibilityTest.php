<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOutcomeReportsGECompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ge_coordinator_course_chooser_includes_courses_with_universal_subjects(): void
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

        $aseDepartment = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'Arts and Sciences Education',
            'is_deleted' => false,
        ]);

        $programDepartment = Department::create([
            'department_code' => 'TBSITD',
            'department_description' => 'BSIT Department',
            'is_deleted' => false,
        ]);

        $programCourse = Course::create([
            'course_code' => 'TBSIT',
            'course_description' => 'BS Information Technology',
            'department_id' => $programDepartment->id,
            'is_deleted' => false,
        ]);

        Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $aseDepartment->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'UNI-101',
            'subject_description' => 'Universal GE Subject',
            'is_universal' => true,
            'department_id' => $aseDepartment->id,
            'course_id' => $programCourse->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'IT-101',
            'subject_description' => 'Program Subject',
            'is_universal' => false,
            'department_id' => $programDepartment->id,
            'course_id' => $programCourse->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        /** @var User $coordinator */
        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $geDepartment->id,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.reports.co-course'));

        $response->assertOk();
        $response->assertSeeText('TBSIT');
    }

    public function test_chairperson_course_report_excludes_universal_subjects_from_program_subject_list(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'General Education',
            'is_deleted' => false,
        ]);

        $programDepartment = Department::create([
            'department_code' => 'TBSND',
            'department_description' => 'Nursing Department',
            'is_deleted' => false,
        ]);

        $programCourse = Course::create([
            'course_code' => 'TBSN',
            'course_description' => 'BS Nursing',
            'department_id' => $programDepartment->id,
            'is_deleted' => false,
        ]);

        /** @var User $chairperson */
        $chairperson = User::factory()->create([
            'role' => 1,
            'department_id' => $programDepartment->id,
            'course_id' => $programCourse->id,
            'is_active' => true,
        ]);

        Subject::create([
            'subject_code' => 'GE-UNIV-1',
            'subject_description' => 'Universal GE Subject',
            'is_universal' => true,
            'department_id' => $programDepartment->id,
            'course_id' => $programCourse->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'NURS-101',
            'subject_description' => 'Foundations of Nursing',
            'is_universal' => false,
            'department_id' => $programDepartment->id,
            'course_id' => $programCourse->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        $response = $this
            ->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('chairperson.reports.co-course', [
                'course_id' => $programCourse->id,
            ]));

        $response->assertOk();
        $response->assertSeeText('NURS-101');
        $response->assertDontSeeText('GE-UNIV-1');
    }
}
