<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseOutcomes;
use App\Models\Department;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseOutcomeSummaryTemporaryThresholdPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructor_summary_view_renders_temporary_co_target_preview_contract(): void
    {
        $instructor = User::factory()->create(['role' => 0]);

        ['period' => $period, 'subject' => $subject, 'co' => $co] = $this->createSubjectWithSummaryData($instructor, 82);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.course-outcome-attainments.subject', [
                'subject' => $subject->id,
                'view' => 'copasssummary',
            ]));

        $response->assertOk();
        $response->assertSee('id="summary-target-level-form"', false);
        $response->assertSee('data-co-target-input="true"', false);
        $response->assertSee('id="co-target-' . $co->id . '"', false);
        $response->assertSee('data-co-target-label="true"', false);
        $response->assertSee('data-met-target-count-cell="true"', false);
        $response->assertSee('data-met-target-percentage-cell="true"', false);
        $response->assertSee('id="co-threshold-preview-data"', false);
        $response->assertSee('"initial_targets"', false);
        $response->assertSee('"' . $co->id . '":' . $co->target_percentage, false);
        $response->assertSee('action="javascript:void(0)"', false);
    }

    public function test_vpaa_subject_summary_view_uses_same_temporary_preview_contract(): void
    {
        $vpaa = User::factory()->create(['role' => 5]);
        $instructor = User::factory()->create(['role' => 0]);

        ['period' => $period, 'subject' => $subject, 'co' => $co] = $this->createSubjectWithSummaryData($instructor, 88);

        $response = $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('vpaa.reports.attainment.subject', [
                'subject' => $subject->id,
                'view' => 'copasssummary',
            ]));

        $response->assertOk();
        $response->assertSee('data-co-target-input="true"', false);
        $response->assertSee('id="co-target-' . $co->id . '"', false);
        $response->assertSee('data-met-target-count-cell="true"', false);
        $response->assertSee('data-met-target-percentage-cell="true"', false);
        $response->assertSee('id="co-threshold-preview-data"', false);
        $response->assertSee('"initial_targets"', false);
        $response->assertSee('"' . $co->id . '":' . $co->target_percentage, false);
        $response->assertSee('action="javascript:void(0)"', false);
    }

    /**
     * @return array{period: AcademicPeriod, subject: Subject, co: CourseOutcomes}
     */
    private function createSubjectWithSummaryData(User $owner, int $targetPercentage): array
    {
        $department = Department::create([
            'department_code' => 'DP-' . Str::upper(Str::random(4)),
            'department_description' => 'Department ' . Str::upper(Str::random(5)),
            'is_deleted' => false,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $course = Course::create([
            'course_code' => 'CRS-' . Str::upper(Str::random(6)),
            'course_description' => 'Course ' . Str::upper(Str::random(5)),
            'department_id' => $department->id,
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'SUB-' . Str::upper(Str::random(6)),
            'subject_description' => 'Subject ' . Str::upper(Str::random(5)),
            'department_id' => $department->id,
            'course_id' => $course->id,
            'academic_period_id' => $period->id,
            'instructor_id' => $owner->id,
            'is_deleted' => false,
        ]);

        $courseOutcome = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'SUB.1',
            'description' => 'Test outcome',
            'target_percentage' => $targetPercentage,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
            'is_deleted' => false,
        ]);

        Activity::create([
            'subject_id' => $subject->id,
            'term' => 'prelim',
            'type' => 'quiz',
            'title' => 'Quiz 1',
            'course_outcome_id' => $courseOutcome->id,
            'number_of_items' => 50,
            'is_deleted' => false,
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);

        return [
            'period' => $period,
            'subject' => $subject,
            'co' => $courseOutcome,
        ];
    }
}
