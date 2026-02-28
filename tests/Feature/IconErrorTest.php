<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class IconErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_layout_renders_without_icon_error(): void
    {
        // Create user with permission to see coach section
        $user = User::factory()->create(['is_active' => true]);
        $permission = Permission::create(['name' => 'view_member_section']);
        $manageTeamsPermission = Permission::create(['name' => 'manage_teams']);
        $user->givePermissionTo($permission);
        $user->givePermissionTo($manageTeamsPermission);

        $response = $this->actingAs($user)->get('/clenska-sekce/profil');

        $response->assertStatus(200);
    }

    public function test_heroicon_o_users_is_valid(): void
    {
        $view = $this->blade('<x-dynamic-component component="heroicon-o-users" class="w-5 h-5" />');
        $view->assertSee('svg');
    }

    public function test_heroicon_o_user_group_is_valid(): void
    {
        $view = $this->blade('<x-dynamic-component component="heroicon-o-user-group" class="w-5 h-5" />');
        $view->assertSee('svg');
    }

    public function test_other_common_icons_are_valid(): void
    {
        $icons = [
            'heroicon-o-academic-cap',
            'heroicon-o-trophy',
            'heroicon-o-star',
            'heroicon-o-clock',
            'heroicon-o-map-pin',
            'heroicon-o-ellipsis-horizontal',
            'heroicon-o-banknotes',
            'heroicon-o-check-badge',
            'heroicon-o-arrow-down-left',
            'heroicon-o-check',
            'heroicon-o-qr-code',
            'heroicon-o-chevron-right',
            'heroicon-o-home',
            'heroicon-o-calendar-days',
            'heroicon-o-user',
            'heroicon-o-credit-card',
            'heroicon-o-chart-bar',
        ];

        foreach ($icons as $icon) {
            $view = $this->blade('<x-dynamic-component :component="$icon" class="w-5 h-5" />', ['icon' => $icon]);
            $view->assertSee('svg');
        }
    }
}
