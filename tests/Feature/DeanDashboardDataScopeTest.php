<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeanDashboardDataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_dashboard_scopes_instructors_and_course_distribution_to_dean_department(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $psyDepartment = Department::create([
            'department_code' => 'ASE',
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        $itDepartment = Department::create([
            'department_code' => 'SBISM',
            'department_description' => 'School of Business, Information Science and Management',
            'is_deleted' => false,
        ]);

        /** @var User $dean */
        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $psyDepartment->id,
        ]);

        Course::create([
            'course_code' => 'BSPSY',
            'course_description' => 'Bachelor of Science in Psychology',
            'department_id' => $psyDepartment->id,
            'is_deleted' => false,
        ]);

        $bsit = Course::create([
            'course_code' => 'BSIT',
            'course_description' => 'Bachelor of Science in Information Technology',
            'department_id' => $itDepartment->id,
            'is_deleted' => false,
        ]);

        $bspsy = Course::where('course_code', 'BSPSY')->firstOrFail();

        User::factory()->createOne([
            'role' => 0,
            'department_id' => $psyDepartment->id,
            'is_active' => true,
        ]);

        User::factory()->createOne([
            'role' => 0,
            'department_id' => $psyDepartment->id,
            'is_active' => false,
        ]);

        User::factory()->createOne([
            'role' => 0,
            'department_id' => $itDepartment->id,
            'is_active' => true,
        ]);

        Student::create([
            'first_name' => 'Psy',
            'last_name' => 'Student',
            'department_id' => $psyDepartment->id,
            'course_id' => $bspsy->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'It',
            'last_name' => 'Student',
            'department_id' => $itDepartment->id,
            'course_id' => $bsit->id,
            'academic_period_id' => $period->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalInstructors', 1);
        $response->assertViewHas('studentsPerCourse', function ($studentsPerCourse) {
            return $studentsPerCourse->has('BSPSY')
                && ! $studentsPerCourse->has('BSIT');
        });
        $response->assertSee('BSPSY');
        $response->assertDontSee('BSIT');
    }
}
