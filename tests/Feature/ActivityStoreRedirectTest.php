<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityStoreRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_redirects_to_manage_activities_with_subject_and_term(): void
    {
        /** @var User $instructor */
        $instructor = User::factory()->createOne([
            'role' => 0,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'TEST-101',
            'subject_description' => 'Test Subject',
            'academic_period_id' => $period->id,
            'instructor_id' => $instructor->id,
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($instructor)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('instructor.activities.store'), [
                'subject_id' => $subject->id,
                'term' => 'prelim',
                'type' => 'exam',
                'title' => 'Unit Test Exam',
                'number_of_items' => 50,
                'create_single' => 1,
            ]);

        $response->assertRedirect(route('instructor.activities.create', [
            'subject_id' => $subject->id,
            'term' => 'prelim',
        ], false));

        $this->assertDatabaseHas('activities', [
            'subject_id' => $subject->id,
            'term' => 'prelim',
            'type' => 'exam',
            'title' => 'Unit Test Exam',
            'is_deleted' => false,
        ]);
    }
}
