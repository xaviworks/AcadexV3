<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicPeriodSelectionRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_an_academic_period_defaults_to_dashboard_when_no_redirect_is_provided(): void
    {
        $instructor = User::factory()->create([
            'role' => 0,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($instructor)->post(route('set.academicPeriod'), [
            'academic_period_id' => $period->id,
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('active_academic_period_id', $period->id);
    }

    public function test_academic_period_selection_returns_to_saved_protected_page(): void
    {
        $geCoordinator = User::factory()->create([
            'role' => 4,
        ]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $this->actingAs($geCoordinator)
            ->get('/gecoordinator/instructors')
            ->assertRedirect(route('select.academicPeriod'));

        $response = $this->actingAs($geCoordinator)->post(route('set.academicPeriod'), [
            'academic_period_id' => $period->id,
        ]);

        $response->assertRedirect('/gecoordinator/instructors');
        $response->assertSessionHas('active_academic_period_id', $period->id);
    }
}
