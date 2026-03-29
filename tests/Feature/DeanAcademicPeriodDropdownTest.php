<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeanAcademicPeriodDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_login_redirects_to_dashboard_instead_of_selection_page(): void
    {
        $dean = User::factory()->create([
            'role' => 2,
        ]);

        $response = $this->post('/login', [
            'email' => $dean->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($dean);
    }

    public function test_dean_routes_auto_select_the_latest_academic_period_when_missing_from_session(): void
    {
        $dean = User::factory()->create([
            'role' => 2,
        ]);

        $latestPeriod = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2024-2025',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)->get(route('dean.grades'));

        $response->assertOk();
        $response->assertSessionHas('active_academic_period_id', $latestPeriod->id);
    }

    public function test_dean_dashboard_seeds_the_academic_period_for_dropdown_usage(): void
    {
        $dean = User::factory()->create([
            'role' => 2,
        ]);

        $latestPeriod = AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($dean)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSessionHas('active_academic_period_id', $latestPeriod->id);
    }
}
