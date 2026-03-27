<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /** @test */
    public function only_admins_can_access_debug_operations()
    {
        $coach = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $coach->assignRole('coach');

        $admin = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $admin->assignRole('admin');
        // Admin role has all permissions including access_admin and manage_advanced_settings

        $this->actingAs($coach)
            ->get('/admin/debug-operations')
            ->assertRedirect('/admin'); // Filament redirects to panel home if unauthorized

        $this->actingAs($admin)
            ->get('/admin/debug-operations')
            ->assertOk();
    }

    /** @test */
    public function only_admins_can_access_season_renewal()
    {
        $coach = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $coach->assignRole('coach');

        $admin = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($coach)
            ->get('/admin/season-renewal')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/season-renewal')
            ->assertOk();
    }

    /** @test */
    public function only_editors_can_access_posts()
    {
        $coach = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $coach->assignRole('coach');

        $editor = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $editor->assignRole('editor');

        $this->actingAs($coach)
            ->get('/admin/posts')
            ->assertForbidden();

        $this->actingAs($editor)
            ->get('/admin/posts')
            ->assertOk();
    }

    /** @test */
    public function only_admins_can_see_roles_field_in_user_form()
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $admin->assignRole('admin');

        $userEditor = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $userEditor->givePermissionTo('manage_users');
        $userEditor->givePermissionTo('access_admin');

        // Note: Testing Livewire component field visibility is complex,
        // but we can at least check if the field is present in the response
        // if we use Filament's internal state.
        // For now, we trust the 'visible' logic in the schema is correct,
        // but we can test if the admin can actually change roles vs user editor.

        $targetUser = User::factory()->create();

        $this->actingAs($userEditor)
            ->get("/admin/users/{$targetUser->id}/edit")
            ->assertDontSee('Role uživatele'); // Label of the roles field

        $this->actingAs($admin)
            ->get("/admin/users/{$targetUser->id}/edit")
            ->assertSee('Role uživatele');
    }

    /** @test */
    public function coaches_cannot_see_all_finance_payments()
    {
        $coach = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $coach->assignRole('coach'); // Coach has access_admin but should not see economy

        $this->actingAs($coach)
            ->get('/admin/finance-payments')
            ->assertForbidden();

        $financeAdmin = User::factory()->create([
            'is_active' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => 'dummy',
        ]);
        $financeAdmin->givePermissionTo('manage_economy');
        $financeAdmin->givePermissionTo('access_admin');

        $this->actingAs($financeAdmin)
            ->get('/admin/finance-payments')
            ->assertOk();
    }
}
