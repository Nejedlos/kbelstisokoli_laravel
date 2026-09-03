<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileLoginTest extends TestCase
{
    public function test_authenticated_member_reopening_login_goes_to_member_dashboard(): void
    {
        $member = $this->createMember();
        $this->actingAs($member)->get('/admin/login')->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
        $this->get('/admin')->assertForbidden();
    }

    public function test_authenticated_member_cannot_be_redirected_to_admin_by_login_page(): void
    {
        $this->actingAs($this->createMember())
            ->withSession(['url.intended' => url('/admin/users')])
            ->get('/admin/login')->assertRedirect(route('member.dashboard'))
            ->assertSessionMissing('url.intended');
    }

    public function test_native_mobile_login_ignores_an_admin_destination(): void
    {
        $member = $this->createMember();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36')
            ->withSession(['url.intended' => url('/admin')])
            ->post('/login', ['email' => $member->email, 'password' => 'password'])
            ->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($member);
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_reopening_login_preserves_confirmed_two_factor_authentication(): void
    {
        $admin = $this->with2FA($this->createAdmin());
        $this->actingAs($admin);
        $this->confirm2FA($admin);
        $fingerprint = session('auth.2fa_fingerprint');

        $this->withSession(['url.intended' => url('/admin')])->get('/admin/login')
            ->assertRedirect(url('/admin'))->assertSessionHas('auth.2fa_fingerprint', $fingerprint);
        $this->get('/admin')->assertOk();
    }
}
