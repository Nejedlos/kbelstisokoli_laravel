<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    /**
     * 1) Guest access scénáře.
     */
    public function test_guest_access_rules(): void
    {
        // Guest vidí public route
        $this->get(route('public.home'))->assertStatus(200);

        // Guest na member route -> redirect na login
        $this->get(route('member.dashboard'))->assertRedirect(route('login'));

        // Guest na admin route -> redirect na admin login (Filament specifické)
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    /**
     * 2) Member access scénáře.
     */
    public function test_member_access_rules(): void
    {
        $member = $this->createMember();

        $this->actingAs($member);

        // Member má přístup do member sekce
        $this->get(route('member.dashboard'))->assertStatus(200);

        // Member nemá přístup do admin sekce (Filament canAccessPanel vrací false)
        $this->get('/admin')->assertStatus(403);
    }

    /**
     * 3) Admin access scénáře.
     */
    public function test_admin_access_rules(): void
    {
        $admin = $this->createAdmin();
        $this->with2FA($admin);

        $this->actingAs($admin);

        // Simulujeme úspěšnou 2FA challenge nastavením session klíče, který očekává CheckTwoFactorTimeout middleware
        session(['auth.2fa_confirmed_at' => now()->timestamp]);

        // Admin má přístup do admin sekce
        // Použijeme /admin/ aby se předešlo 301/302 trailing slash redirectu
        $response = $this->get('/admin');
        if ($response->status() !== 200) {
            dump($response->status());
            dump($response->headers->all());
        }
        $response->assertStatus(200);

        // Admin má zároveň přístup do member sekce
        $this->get(route('member.dashboard'))->assertStatus(200);
    }

    /**
     * 4) Admin + 2FA enforcement.
     */
    public function test_admin_2fa_enforcement(): void
    {
        $admin = $this->createAdmin(); // Bez 2FA

        $this->actingAs($admin);

        // Admin bez 2FA nedostane admin access -> redirect na setup
        // Middleware EnsureTwoFactorEnabled přesměrovává na 'auth.two-factor-setup'
        $this->get('/admin')->assertRedirect(route('auth.two-factor-setup'));

        // Member není omylem blokován admin-only 2FA pravidlem
        $member = $this->createMember();
        $this->actingAs($member);
        $this->get(route('member.dashboard'))->assertStatus(200);
    }

    /**
     * 5) Logout.
     */
    public function test_logout_invalidates_session(): void
    {
        $user = $this->createMember();
        $this->actingAs($user);

        $this->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
        $this->get(route('member.dashboard'))->assertRedirect(route('login'));
    }

    /**
     * Login redirect logic.
     */
    public function test_login_redirects_correctly(): void
    {
        $member = $this->createMember(['password' => Hash::make('password')]);

        $response = $this->post(route('login'), [
            'email' => $member->email,
            'password' => 'password',
        ]);

        // Běžní členové jsou po 1. fázi přihlášení vedení do členské sekce
        $response->assertRedirect(route('member.dashboard'));
    }

    /**
     * 7) Admin + Member scénáře (Kombinované role).
     */
    public function test_admin_and_member_access(): void
    {
        $user = $this->createAdmin();
        $user->assignRole('player');
        $this->with2FA($user);

        $this->actingAs($user);
        session(['auth.2fa_confirmed_at' => now()->timestamp]);

        // Má přístup do obou sekcí
        $this->get('/admin/')->assertStatus(200);
        $this->get(route('member.dashboard'))->assertStatus(200);
    }

    /**
     * 8) Inactive user scénáře.
     */
    public function test_inactive_user_cannot_access(): void
    {
        $user = $this->createMember(['is_active' => false]);

        $this->actingAs($user);

        // Middleware 'active' odhlásí uživatele a přesměruje na login s chybou
        $response = $this->get(route('member.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => 'Účet je deaktivován. Kontaktujte správce.']);
        $this->assertGuest();
    }
}
