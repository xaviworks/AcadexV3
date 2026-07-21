<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_login_statistics_on_sqlite(): void
    {
        $admin = User::factory()->create([
            'role' => 3,
        ]);

        UserLog::forceCreate([
            'user_id' => $admin->id,
            'event_type' => 'login',
            'created_at' => now()->setTime(9, 30),
            'updated_at' => now()->setTime(9, 30),
        ]);

        UserLog::forceCreate([
            'user_id' => $admin->id,
            'event_type' => 'failed_login',
            'created_at' => now()->setTime(10, 30),
            'updated_at' => now()->setTime(10, 30),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('loginCount', 1);
        $response->assertViewHas('failedLoginCount', 1);
    }
}
