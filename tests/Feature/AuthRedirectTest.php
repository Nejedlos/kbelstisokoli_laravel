<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AuthRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('filament.panels.admin.path', 'admin');
        Role::findOrCreate('admin', 'web');
    }

    public function test_member_redirects_to_dashboard_by_default(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        // No admin role

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/clenska-sekce/dashboard', $url);
    }

    public function test_admin_redirects_to_admin_by_default(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/admin', $url);
    }

    public function test_member_respects_intended_url(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Session::put('url.intended', url('/clenska-sekce/profil'));

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals(url('/clenska-sekce/profil'), $url);
    }

    public function test_member_ignores_admin_intended_url(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Session::put('url.intended', url('/admin/users'));

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/clenska-sekce/dashboard', $url);
    }

    public function test_admin_respects_intended_url(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');
        Session::put('url.intended', url('/admin/settings'));

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals(url('/admin/settings'), $url);
    }

    public function test_admin_prefers_admin_over_general_member_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin');
        Session::put('url.intended', url('/clenska-sekce/dashboard'));

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/admin', $url);
    }

    public function test_ignores_login_and_logout_intended_urls(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        Session::put('url.intended', url('/login'));

        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/clenska-sekce/dashboard', $url);

        Session::put('url.intended', url('/logout'));
        $url = AuthRedirect::getTargetUrl($user);
        $this->assertEquals('/clenska-sekce/dashboard', $url);
    }
}
