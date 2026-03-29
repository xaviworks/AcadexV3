<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcademicPeriodGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_next_academic_period_even_when_latest_created_row_is_summer(): void
    {
        $admin = User::factory()->createOne([
            'role' => 3,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2025',
            'semester' => 'Summer',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026',
            'semester' => 'Summer',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.academicPeriods.generate'));

        $response->assertRedirect(route('admin.academicPeriods'));
        $response->assertSessionHas('success', 'New academic periods generated successfully.');

        $this->assertDatabaseHas('academic_periods', [
            'academic_year' => '2027-2028',
            'semester' => '1st',
        ]);

        $this->assertDatabaseHas('academic_periods', [
            'academic_year' => '2027-2028',
            'semester' => '2nd',
        ]);

        $this->assertDatabaseHas('academic_periods', [
            'academic_year' => '2027',
            'semester' => 'Summer',
        ]);
    }

    public function test_generator_completes_missing_entries_for_next_academic_period(): void
    {
        $admin = User::factory()->createOne([
            'role' => 3,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026-2027',
            'semester' => '2nd',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2026',
            'semester' => 'Summer',
            'is_deleted' => false,
        ]);

        AcademicPeriod::create([
            'academic_year' => '2027',
            'semester' => 'Summer',
            'is_deleted' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.academicPeriods.generate'));

        $response->assertRedirect(route('admin.academicPeriods'));
        $response->assertSessionHas('success', 'Academic period 2027-2028 was completed by creating the missing semester entries.');

        $this->assertDatabaseHas('academic_periods', [
            'academic_year' => '2027-2028',
            'semester' => '1st',
        ]);

        $this->assertDatabaseHas('academic_periods', [
            'academic_year' => '2027-2028',
            'semester' => '2nd',
        ]);
    }
}
