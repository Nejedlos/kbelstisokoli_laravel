<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles for testing
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test that guest is redirected to login when accessing admin.
     * ID: TEST-ADMIN-01
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        // Na tomto projektu vrací bootstrap/app.php vlastní 401 chybu s view
        $response->assertStatus(401);
    }

    /**
     * Test that regular user without admin roles/permissions is forbidden from admin.
     * ID: TEST-ADMIN-02
     */
    public function test_regular_user_cannot_access_admin(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);
        // Assign a non-admin role
        $user->assignRole('player');

        $response = $this->actingAs($user)->get('/admin');

        // Filament usually returns 403 or redirects if cannot access panel
        $response->assertStatus(403);
    }

    /**
     * Test that inactive admin user cannot access admin.
     * ID: TEST-ADMIN-03
     */
    public function test_inactive_admin_cannot_access_admin(): void
    {
        $admin = User::factory()->create([
            'is_active' => false,
        ]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Test that active admin user can access admin.
     * ID: TEST-ADMIN-04
     */
    public function test_active_admin_can_access_admin(): void
    {
        $admin = $this->createAdmin();

        // Obcházíme 2FA challenge v testu pomocí screenshot režimu (?screenshot=1)
        // Musíme také poslat X-Screenshot-Token pro bypass v DetectScreenshotMode, pokud je vyžadován
        $response = $this->actingAs($admin)->get('/admin?screenshot=1');

        $response->assertStatus(200);
    }

    /**
     * Test that active coach can access admin.
     * ID: TEST-ADMIN-05
     */
    public function test_active_coach_can_access_admin(): void
    {
        $coach = User::factory()->create([
            'is_active' => true,
        ]);
        $coach->assignRole('coach');

        $response = $this->actingAs($coach)->get('/admin?screenshot=1');

        $response->assertStatus(200);
    }

    /**
     * Test logic of canAccessAdmin method on User model.
     * ID: TEST-ADMIN-06
     */
    public function test_user_model_can_access_admin_logic(): void
    {
        $user = User::factory()->make(['is_active' => true]);
        $this->assertFalse($user->canAccessAdmin(), 'User without roles should not have access');

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');
        $this->assertTrue($admin->canAccessAdmin(), 'Active admin should have access');

        $inactiveAdmin = User::factory()->create(['is_active' => false]);
        $inactiveAdmin->assignRole('admin');
        $this->assertFalse($inactiveAdmin->canAccessAdmin(), 'Inactive admin should not have access');

        $editor = User::factory()->create(['is_active' => true]);
        $editor->assignRole('editor');
        $this->assertTrue($editor->canAccessAdmin(), 'Active editor should have access');
    }
}
