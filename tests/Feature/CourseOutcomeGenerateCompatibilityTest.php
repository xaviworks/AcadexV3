<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\CourseOutcomes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CourseOutcomeGenerateCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_course_outcomes_succeeds_without_target_percentage_column(): void
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        /** @var User $coordinator */
        $coordinator = User::factory()->createOne(['role' => 4]);

        $subject = Subject::create([
            'subject_code' => 'GE101',
            'subject_description' => 'Understanding the Self',
            'is_universal' => true,
            'year_level' => 1,
            'academic_period_id' => $period->id,
            'is_deleted' => false,
        ]);

        if (Schema::hasColumn('course_outcomes', 'target_percentage')) {
            Schema::table('course_outcomes', function ($table) {
                $table->dropColumn('target_percentage');
            });
        }

        $response = $this
            ->actingAs($coordinator)
            ->from(route('gecoordinator.course_outcomes.index'))
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.course_outcomes.generate'), [
                'generation_mode' => 'missing_only',
                'year_levels' => ['all'],
            ]);

        $response->assertRedirect(route('gecoordinator.course_outcomes.index'));
        $response->assertSessionHas('success');

        $this->assertSame(
            6,
            CourseOutcomes::where('subject_id', $subject->id)->where('is_deleted', false)->count()
        );
    }
}
