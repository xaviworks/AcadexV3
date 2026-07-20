<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\CourseOutcomes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOutcomeAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_instructor_cannot_view_or_update_an_unassigned_subjects_course_outcome(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);
        $instructor = User::factory()->create(['role' => 0]);
        $otherInstructor = User::factory()->create(['role' => 0]);
        $subject = Subject::create([
            'subject_code' => 'IT102',
            'subject_description' => 'Programming 1',
            'academic_period_id' => $period->id,
            'instructor_id' => $otherInstructor->id,
            'is_deleted' => false,
        ]);
        $courseOutcome = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT102.1',
            'description' => 'Protected outcome',
            'target_percentage' => 75,
            'created_by' => $otherInstructor->id,
            'updated_by' => $otherInstructor->id,
            'is_deleted' => false,
        ]);

        $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('instructor.course_outcomes.show', $courseOutcome))
            ->assertForbidden();

        $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->put(route('instructor.course_outcomes.update', $courseOutcome), [
                'co_code' => 'CO1',
                'co_identifier' => 'IT102.1',
                'description' => 'Attempted update',
                'target_percentage' => 75,
            ])
            ->assertForbidden();
    }

    public function test_ge_coordinator_can_create_an_outcome_for_a_universal_subject(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);
        $coordinator = User::factory()->create(['role' => 4]);
        $subject = Subject::create([
            'subject_code' => 'GE101',
            'subject_description' => 'Understanding the Self',
            'academic_period_id' => $period->id,
            'is_universal' => true,
            'is_deleted' => false,
        ]);

        $this->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.course_outcomes.store'), [
                'subject_id' => $subject->id,
                'co_code' => 'CO1',
                'co_identifier' => 'GE101.1',
                'description' => 'GE outcome',
                'target_percentage' => 75,
            ])
            ->assertRedirect(route('gecoordinator.course_outcomes.index', ['subject_id' => $subject->id]));

        $this->assertDatabaseHas('course_outcomes', [
            'subject_id' => $subject->id,
            'co_code' => 'CO1',
        ]);
    }

    public function test_chairperson_without_a_course_cannot_manage_an_unscoped_subject(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);
        $chairperson = User::factory()->create(['role' => 1, 'course_id' => null]);
        $subject = Subject::create([
            'subject_code' => 'IT103',
            'subject_description' => 'Programming 2',
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        $this->actingAs($chairperson)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('chairperson.course_outcomes.store'), [
                'subject_id' => $subject->id,
                'co_code' => 'CO1',
                'co_identifier' => 'IT103.1',
                'description' => 'Unauthorized outcome',
                'target_percentage' => 75,
            ])
            ->assertForbidden();
    }
}
