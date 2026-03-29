<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GECoordinatorManageInstructorsCanonicalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manage_instructors_shows_canonical_ge_instructor_even_without_ge_access_flag(): void
    {
        [$period, $aseDepartment, $geCourse, $coordinator] = $this->seedCanonicalContext();

        $instructor = User::factory()->create([
            'role' => 0,
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'is_active' => true,
            'can_teach_ge' => false,
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.instructors'));

        $response->assertOk();
        $response->assertViewHas('instructors', function ($instructors) use ($instructor) {
            return $instructors->contains('id', $instructor->id);
        });
    }

    public function test_manage_instructors_uses_deactivate_action_for_canonical_ge_instructors(): void
    {
        [$period, $aseDepartment, $geCourse, $coordinator] = $this->seedCanonicalContext();

        $instructor = User::factory()->create([
            'role' => 0,
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'is_active' => true,
            'can_teach_ge' => true,
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.instructors'));

        $response->assertOk();

        $content = $response->getContent();
        $deactivatePattern = '/data-bs-target="#confirmDeactivateModal"[^>]*data-instructor-id="' . $instructor->id . '"/';
        $removePattern = '/data-bs-target="#confirmRemoveGEAccessModal"[^>]*data-instructor-id="' . $instructor->id . '"/';

        $this->assertSame(1, preg_match($deactivatePattern, $content));
        $this->assertSame(0, preg_match($removePattern, $content));
    }

    public function test_deactivate_canonical_ge_instructor_moves_account_to_inactive_list(): void
    {
        [$period, $aseDepartment, $geCourse, $coordinator] = $this->seedCanonicalContext();

        $instructor = User::factory()->create([
            'role' => 0,
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'is_active' => true,
            'can_teach_ge' => true,
        ]);

        $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->post(route('gecoordinator.deactivateInstructor', ['id' => $instructor->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $instructor->id,
            'is_active' => false,
            'can_teach_ge' => false,
        ]);

        $response = $this
            ->actingAs($coordinator)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('gecoordinator.instructors'));

        $response->assertOk();
        $response->assertViewHas('instructors', function ($instructors) use ($instructor) {
            return $instructors->contains(function ($item) use ($instructor) {
                return (int) $item->id === (int) $instructor->id && (bool) $item->is_active === false;
            });
        });

        $content = $response->getContent();
        $activatePattern = '/data-bs-target="#confirmActivateModal"[^>]*data-id="' . $instructor->id . '"/';

        $this->assertSame(1, preg_match($activatePattern, $content));
    }

    /**
     * @return array{0: AcademicPeriod, 1: Department, 2: Course, 3: User}
     */
    private function seedCanonicalContext(): array
    {
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $aseDepartment = Department::firstOrCreate([
            'department_code' => 'ASE',
        ], [
            'department_description' => 'School of Arts and Science and Education',
            'is_deleted' => false,
        ]);

        Department::firstOrCreate([
            'department_code' => 'GE',
        ], [
            'department_description' => 'School of General Education',
            'is_deleted' => false,
        ]);

        $geCourse = Course::firstOrCreate([
            'course_code' => 'GE',
        ], [
            'course_description' => 'General Education',
            'department_id' => $aseDepartment->id,
            'is_deleted' => false,
        ]);

        $coordinator = User::factory()->create([
            'role' => 4,
            'department_id' => $aseDepartment->id,
            'course_id' => $geCourse->id,
            'is_active' => true,
            'can_teach_ge' => true,
        ]);

        return [$period, $aseDepartment, $geCourse, $coordinator];
    }
}
