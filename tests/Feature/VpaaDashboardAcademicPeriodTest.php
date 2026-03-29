<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VpaaDashboardAcademicPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_academic_period_uses_redirect_target_when_provided(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)->post(route('set.academicPeriod'), [
            'academic_period_id' => $period->id,
            'redirect_to' => '/vpaa/dashboard',
        ]);

        $response->assertRedirect('/vpaa/dashboard');
        $response->assertSessionHas('active_academic_period_id', $period->id);
    }

    public function test_vpaa_dashboard_poll_counts_only_selected_academic_period_data(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $periodOne = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $periodTwo = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        $deptOne = Department::create([
            'department_code' => 'BSIT-' . Str::upper(Str::random(4)),
            'department_description' => 'Department One',
            'is_deleted' => false,
        ]);

        $deptTwo = Department::create([
            'department_code' => 'BSBA-' . Str::upper(Str::random(4)),
            'department_description' => 'Department Two',
            'is_deleted' => false,
        ]);

        $courseOne = Course::create([
            'course_code' => 'C1-' . Str::upper(Str::random(6)),
            'course_description' => 'Course One',
            'department_id' => $deptOne->id,
            'is_deleted' => false,
        ]);

        $courseTwo = Course::create([
            'course_code' => 'C2-' . Str::upper(Str::random(6)),
            'course_description' => 'Course Two',
            'department_id' => $deptTwo->id,
            'is_deleted' => false,
        ]);

        $instructorOne = User::factory()->createOne([
            'role' => 0,
            'is_active' => true,
            'department_id' => $deptOne->id,
        ]);

        $instructorTwo = User::factory()->createOne([
            'role' => 0,
            'is_active' => true,
            'department_id' => $deptTwo->id,
        ]);

        Subject::create([
            'subject_code' => 'SUB1-' . Str::upper(Str::random(6)),
            'subject_description' => 'Subject One',
            'year_level' => 1,
            'department_id' => $deptOne->id,
            'course_id' => $courseOne->id,
            'academic_period_id' => $periodOne->id,
            'instructor_id' => $instructorOne->id,
            'is_deleted' => false,
        ]);

        Subject::create([
            'subject_code' => 'SUB2-' . Str::upper(Str::random(6)),
            'subject_description' => 'Subject Two',
            'year_level' => 1,
            'department_id' => $deptTwo->id,
            'course_id' => $courseTwo->id,
            'academic_period_id' => $periodTwo->id,
            'instructor_id' => $instructorTwo->id,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Alice',
            'last_name' => 'PeriodOne',
            'department_id' => $deptOne->id,
            'course_id' => $courseOne->id,
            'academic_period_id' => $periodOne->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        Student::create([
            'first_name' => 'Bob',
            'last_name' => 'PeriodTwo',
            'department_id' => $deptTwo->id,
            'course_id' => $courseTwo->id,
            'academic_period_id' => $periodTwo->id,
            'year_level' => 1,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $periodOne->id])
            ->getJson(route('vpaa.dashboard.poll'));

        $response->assertOk();
        $response->assertJsonPath('departmentsCount', 1);
        $response->assertJsonPath('instructorsCount', 2);
        $response->assertJsonPath('studentsCount', 1);
        $response->assertJsonPath('academicPrograms', 1);
    }
}
