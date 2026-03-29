<?php

namespace Tests\Feature;

use App\Models\HelpGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpGuideAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_help_guides_targeted_to_admins(): void
    {
        $admin = User::factory()->create(['role' => HelpGuide::ROLE_ADMIN]);

        $guide = HelpGuide::create([
            'title' => 'Admin onboarding',
            'content' => '<p>Admin-specific instructions.</p>',
            'visible_roles' => [HelpGuide::ROLE_ADMIN],
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('help-guides.index'))
            ->assertOk()
            ->assertSee($guide->title);
    }

    public function test_non_admin_users_do_not_see_admin_only_guides(): void
    {
        $admin = User::factory()->create(['role' => HelpGuide::ROLE_ADMIN]);
        $instructor = User::factory()->create(['role' => HelpGuide::ROLE_INSTRUCTOR]);

        $guide = HelpGuide::create([
            'title' => 'Admin-only maintenance steps',
            'content' => '<p>Only admins should see this.</p>',
            'visible_roles' => [HelpGuide::ROLE_ADMIN],
            'sort_order' => 0,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($instructor)
            ->get(route('help-guides.index'))
            ->assertOk()
            ->assertDontSee($guide->title);
    }

    public function test_admin_can_create_help_guides_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => HelpGuide::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.help-guides.store'), [
                'title' => 'Admin guide from form',
                'content' => '<p>Created through the admin form.</p>',
                'visible_roles' => [HelpGuide::ROLE_ADMIN],
                'sort_order' => 0,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.help-guides.index'));

        $guide = HelpGuide::where('title', 'Admin guide from form')->first();

        $this->assertNotNull($guide);
        $this->assertSame([HelpGuide::ROLE_ADMIN], $guide->visible_roles);
    }
}
