<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpaaGradingConfigurationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vpaa_can_view_grading_configuration(): void
    {
        $vpaa = User::factory()->create(['role' => 5]);
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $this->actingAs($vpaa)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('vpaa.gradingConfiguration.index'))
            ->assertOk();
    }

    public function test_admin_cannot_access_vpaa_grading_configuration(): void
    {
        $admin = User::factory()->create(['role' => 3]);
        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $this->actingAs($admin)
            ->withSession(['active_academic_period_id' => $period->id])
            ->get(route('vpaa.gradingConfiguration.index'))
            ->assertForbidden();
    }

    public function test_old_admin_grading_urls_are_not_registered(): void
    {
        $admin = User::factory()->create(['role' => 3]);

        $this->actingAs($admin)
            ->get('/admin/grades-formula')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get('/admin/structure-template-requests')
            ->assertNotFound();
    }
}
