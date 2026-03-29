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

class DeanVpaaCoCourseAcademicPeriodIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_co_course_report_only_shows_subjects_from_selected_period(): void
    {
        $vpaa = User::factory()->createOne([
            'role' => 5,
        ]);

        $department = Department::create([
            'department_code' => 'VCC-' . Str::upper(Str::random(4)),
            'department_description' => 'VPAA Co Course Department',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'VPC-' . Str::upper(Str::random(5)),
            'course_description' => 'VPAA Program',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $oldPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $newPeriod = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $inScope = Subject::create([
            'subject_code' => 'VNEW-' . Str::upper(Str::random(6)),
            'subject_description' => 'VPAA In Scope Subject',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $newPeriod->id,
            'is_deleted' => false,
        ]);

        $outOfScope = Subject::create([
            'subject_code' => 'VOLD-' . Str::upper(Str::random(6)),
            'subject_description' => 'VPAA Out Of Scope Subject',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $oldPeriod->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $newPeriod->id])
            ->get(route('vpaa.reports.co-course', ['course_id' => $course->id]));

        $response->assertOk();
        $response->assertSeeText($inScope->subject_code);
        $response->assertDontSeeText($outOfScope->subject_code);
    }

    public function test_dean_co_course_report_only_shows_subjects_from_selected_period(): void
    {
        $department = Department::create([
            'department_code' => 'DCC-' . Str::upper(Str::random(4)),
            'department_description' => 'Dean Co Course Department',
            'is_deleted' => false,
        ]);

        $dean = User::factory()->createOne([
            'role' => 2,
            'department_id' => $department->id,
        ]);

        $course = Course::create([
            'course_code' => 'DPC-' . Str::upper(Str::random(5)),
            'course_description' => 'Dean Program',
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $oldPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $newPeriod = AcademicPeriod::create([
            'academic_year' => '2028-2029',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $inScope = Subject::create([
            'subject_code' => 'DNEW-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean In Scope Subject',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $newPeriod->id,
            'is_deleted' => false,
        ]);

        $outOfScope = Subject::create([
            'subject_code' => 'DOLD-' . Str::upper(Str::random(6)),
            'subject_description' => 'Dean Out Of Scope Subject',
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $oldPeriod->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)
            ->withSession(['active_academic_period_id' => $newPeriod->id])
            ->get(route('dean.reports.co-course', ['course_id' => $course->id]));

        $response->assertOk();
        $response->assertSeeText($inScope->subject_code);
        $response->assertDontSeeText($outOfScope->subject_code);
    }
}
