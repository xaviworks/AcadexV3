<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VpaaAttainmentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_attainment_reports_page_shows_department_cards(): void
    {
        $vpaa = User::factory()->create([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'BSIT',
            'department_description' => 'BSIT Department',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->get(route('vpaa.reports.attainment'));

        $response->assertOk();
        $response->assertSeeText('Select a department to view its subjects');
        $response->assertSeeText($department->department_description);
    }

    public function test_vpaa_attainment_filters_subjects_by_department_and_active_period(): void
    {
        $vpaa = User::factory()->create([
            'role' => 5,
        ]);

        $departmentA = Department::create([
            'department_code' => 'BSCS',
            'department_description' => 'Computer Science',
            'is_deleted' => false,
        ]);

        $departmentB = Department::create([
            'department_code' => 'BSBA',
            'department_description' => 'Business Administration',
            'is_deleted' => false,
        ]);

        $activePeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $otherPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        $courseA = Course::create([
            'course_code' => 'CS-' . Str::upper(Str::random(6)),
            'course_description' => 'BS Computer Science',
            'department_id' => $departmentA->id,
            'is_deleted' => false,
        ]);

        $courseB = Course::create([
            'course_code' => 'BA-' . Str::upper(Str::random(6)),
            'course_description' => 'BS Business Administration',
            'department_id' => $departmentB->id,
            'is_deleted' => false,
        ]);

        $inScopeSubject = Subject::create([
            'subject_code' => 'CS101-' . Str::upper(Str::random(4)),
            'subject_description' => 'Intro to CS',
            'department_id' => $departmentA->id,
            'course_id' => $courseA->id,
            'academic_period_id' => $activePeriod->id,
            'is_deleted' => false,
        ]);

        $otherPeriodSubject = Subject::create([
            'subject_code' => 'CS102-' . Str::upper(Str::random(4)),
            'subject_description' => 'Data Structures',
            'department_id' => $departmentA->id,
            'course_id' => $courseA->id,
            'academic_period_id' => $otherPeriod->id,
            'is_deleted' => false,
        ]);

        $otherDepartmentSubject = Subject::create([
            'subject_code' => 'BA101-' . Str::upper(Str::random(4)),
            'subject_description' => 'Accounting 1',
            'department_id' => $departmentB->id,
            'course_id' => $courseB->id,
            'academic_period_id' => $activePeriod->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $activePeriod->id])
            ->get(route('vpaa.reports.attainment', [
                'department_id' => $departmentA->id,
            ]));

        $response->assertOk();
        $response->assertSeeText($inScopeSubject->subject_code);
        $response->assertDontSeeText($otherPeriodSubject->subject_code);
        $response->assertDontSeeText($otherDepartmentSubject->subject_code);
    }

    public function test_vpaa_can_open_subject_attainment_report_in_read_only_mode(): void
    {
        $vpaa = User::factory()->create([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'BSED',
            'department_description' => 'Education',
            'is_deleted' => false,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'ED-' . Str::upper(Str::random(6)),
            'course_description' => 'BS Education',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'ED101-' . Str::upper(Str::random(4)),
            'subject_description' => 'Foundations of Education',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('vpaa.reports.attainment.subject', [
                'subject' => $subject->id,
            ]));

        $response->assertOk();
        $response->assertSeeText('Back to Subjects');
        $response->assertSeeText($subject->subject_code);
    }

    public function test_non_vpaa_user_is_redirected_from_vpaa_attainment_reports(): void
    {
        $dean = User::factory()->create([
            'role' => 2,
        ]);

        $response = $this->actingAs($dean)
            ->get(route('vpaa.reports.attainment'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_old_vpaa_attainment_path_returns_not_found(): void
    {
        $vpaa = User::factory()->create([
            'role' => 5,
        ]);

        $response = $this->actingAs($vpaa)
            ->get('/vpaa/course-outcome-attainment');

        $response->assertNotFound();
    }
}
