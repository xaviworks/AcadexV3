<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\CourseOutcomes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseOutcomeTargetPercentageTest extends TestCase
{
    use RefreshDatabase;

    public function test_chairperson_can_store_course_outcome_with_target_percentage_bounds(): void
    {
        $chairperson = User::factory()->create(['role' => 1]);
        $subject = $this->createSubjectWithPeriod();

        $response = $this
            ->actingAs($chairperson)
            ->post(route('chairperson.course_outcomes.store'), [
                'subject_id' => $subject->id,
                'co_code' => 'CO1',
                'co_identifier' => 'IT102.1',
                'description' => 'Outcome description',
                'target_percentage' => 100,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_outcomes', [
            'subject_id' => $subject->id,
            'co_code' => 'CO1',
            'target_percentage' => 100,
        ]);
    }

    public function test_store_rejects_target_percentage_above_100(): void
    {
        $chairperson = User::factory()->create(['role' => 1]);
        $subject = $this->createSubjectWithPeriod();

        $response = $this
            ->actingAs($chairperson)
            ->from(route('chairperson.course_outcomes.index', ['subject_id' => $subject->id]))
            ->post(route('chairperson.course_outcomes.store'), [
                'subject_id' => $subject->id,
                'co_code' => 'CO1',
                'co_identifier' => 'IT102.1',
                'description' => 'Outcome description',
                'target_percentage' => 101,
            ]);

        $response->assertSessionHasErrors(['target_percentage']);
    }

    public function test_chairperson_can_update_target_percentage(): void
    {
        $chairperson = User::factory()->create(['role' => 1]);
        $subject = $this->createSubjectWithPeriod();

        $courseOutcome = CourseOutcomes::create([
            'subject_id' => $subject->id,
            'academic_period_id' => $subject->academic_period_id,
            'co_code' => 'CO1',
            'co_identifier' => 'IT102.1',
            'description' => 'Initial description',
            'target_percentage' => 75,
            'created_by' => $chairperson->id,
            'updated_by' => $chairperson->id,
            'is_deleted' => false,
        ]);

        $response = $this
            ->actingAs($chairperson)
            ->put(route('chairperson.course_outcomes.update', $courseOutcome), [
                'co_code' => 'CO1',
                'co_identifier' => 'IT102.1',
                'description' => 'Updated description',
                'target_percentage' => 0,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_outcomes', [
            'id' => $courseOutcome->id,
            'target_percentage' => 0,
        ]);
    }

    private function createSubjectWithPeriod(): Subject
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        return Subject::create([
            'subject_code' => 'IT102',
            'subject_description' => 'Computer Programming 1',
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);
    }
}
