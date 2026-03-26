<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SqliteMigrationCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ge_subject_requests_accept_revoked_status_under_sqlite(): void
    {
        $instructor = User::factory()->create();
        $requester = User::factory()->create();

        DB::table('g_e_subject_requests')->insert([
            'instructor_id' => $instructor->id,
            'requested_by' => $requester->id,
            'status' => 'revoked',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('g_e_subject_requests', [
            'instructor_id' => $instructor->id,
            'status' => 'revoked',
        ]);
    }

    public function test_user_logs_accept_new_session_event_types_under_sqlite(): void
    {
        $user = User::factory()->create();

        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'event_type' => '2fa_reset_by_admin',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'browser' => 'Test',
            'device' => 'Desktop',
            'platform' => 'SQLite',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $user->id,
            'event_type' => '2fa_reset_by_admin',
        ]);
    }

    public function test_activities_accept_formula_driven_custom_types_under_sqlite(): void
    {
        $instructor = User::factory()->create(['role' => 0]);

        $period = AcademicPeriod::create([
            'academic_year' => '2025-2026',
            'semester' => '1st',
            'is_deleted' => false,
        ]);

        $subject = Subject::create([
            'subject_code' => 'NURS-201',
            'subject_description' => 'Skills Laboratory',
            'academic_period_id' => $period->id,
            'instructor_id' => $instructor->id,
            'is_deleted' => false,
        ]);

        DB::table('activities')->insert([
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'term' => 'prelim',
            'type' => 'skills.return_demo',
            'title' => 'Return Demo 1',
            'number_of_items' => 10,
            'is_deleted' => false,
            'created_by' => $instructor->id,
            'updated_by' => $instructor->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('activities', [
            'subject_id' => $subject->id,
            'type' => 'skills.return_demo',
        ]);
    }
}
