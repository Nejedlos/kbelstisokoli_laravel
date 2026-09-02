<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\TwoFactorService;
use App\Support\AuthRedirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorPipelineTest extends TestCase
{
    private function code(User $user): string
    {
        return (new Google2FA)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));
    }

    private function filamentLogin(User $user, bool $remember = false): TestResponse
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
        $snapshot = collect($matches[1])->map(fn ($value) => html_entity_decode($value, ENT_QUOTES))
            ->first(fn ($value) => json_decode($value, true)['memo']['name'] === Login::class);
        $this->assertNotNull($snapshot, json_encode(collect($matches[1])->map(fn ($value) => json_decode(html_entity_decode($value, ENT_QUOTES), true)['memo']['name'])->all()));

        return $this->withHeader('X-Livewire', 'true')->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => $snapshot,
                'updates' => ['data.email' => $user->email, 'data.password' => 'password', 'data.remember' => $remember],
                'calls' => [['method' => 'authenticate', 'params' => []]],
            ]],
        ]);
    }

    public function test_every_admin_role_and_direct_permission_requires_setup_across_the_application(): void
    {
        $economist = Role::findOrCreate('economist', 'web');
        $economist->givePermissionTo(['access_admin', 'view_member_section']);

        foreach (['super_admin', 'admin', 'coach', 'editor', 'economist', 'direct'] as $role) {
            $user = $this->createMember();
            $this->assertFalse($user->canAccessAdmin());
            $role === 'direct' ? $user->givePermissionTo('access_admin') : $user->assignRole($role);
            $this->assertTrue($user->canAccessAdmin());
            $this->actingAs($user);

            foreach (['/admin', route('member.dashboard'), route('member.profile.edit'), route('public.home'), '/admin/users/search-ajax'] as $url) {
                $this->get($url)->assertRedirect(route('auth.two-factor-setup'));
            }
            $this->post(route('member.profile.update'), ['name' => 'Unauthorized change'])->assertRedirect(route('auth.two-factor-setup'));
            $this->assertNotEquals('Unauthorized change', $user->fresh()->name);
            $this->post(route('default-livewire.update'), ['components' => []])->assertRedirect(route('auth.two-factor-setup'));
            $this->assertTrue($user->hasRole('player'));
        }
    }

    public function test_regular_members_default_to_password_only_in_both_login_flows(): void
    {
        $user = $this->createMember();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
        $this->get('/admin')->assertForbidden();
        $this->post(route('logout'));

        $this->filamentLogin($user)->assertOk()->assertJsonPath('components.0.effects.redirect', route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_filament_login_challenges_a_member_with_optional_2fa(): void
    {
        $user = $this->with2FA($this->createMember());
        $this->filamentLogin($user, true)->assertOk()->assertJsonPath('components.0.effects.redirect', route('two-factor.login'));
        $this->assertGuest();
        $this->assertEquals($user->id, session('login.id'));
        $this->assertTrue(session('login.remember'));
        $this->get(route('two-factor.login'))->assertOk();
        $this->post(route('two-factor.login.store'), ['code' => $this->code($user)])->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_admin_login_requires_setup_and_confirmation_before_any_access(): void
    {
        $user = $this->createAdmin();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertDontSee(route('member.profile.edit'), false);
        $this->from(route('auth.two-factor-setup'))->post(route('two-factor.enable'))->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee('data:image/svg+xml', false);
        $this->get(route('member.dashboard'))->assertRedirect(route('auth.two-factor-setup'));
        $this->post(route('two-factor.confirm'), ['code' => $this->code($user)])->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee($user->fresh()->recoveryCodes()[0]);
        $this->get(route('auth.two-factor-complete'))->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
        $this->get('/admin')->assertOk();
        $this->delete(route('two-factor.disable'))->assertForbidden();
        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_filament_admin_login_without_2fa_goes_directly_to_setup(): void
    {
        $user = $this->createAdmin();
        $this->filamentLogin($user)->assertOk()->assertJsonPath('components.0.effects.redirect', route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk();
        $this->get(route('member.dashboard'))->assertRedirect(route('auth.two-factor-setup'));
    }

    public function test_member_can_complete_setup_from_profile_including_password_confirmation(): void
    {
        $user = $this->createMember();
        $this->actingAs($user)->get(route('member.profile.edit'))->assertOk()->assertSee(route('auth.two-factor-setup'), false);
        $this->get(route('auth.two-factor-setup'))->assertRedirect(route('password.confirm'));
        $this->get(route('password.confirm'))->assertOk();
        $this->post(route('password.confirm.store'), ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->post(route('password.confirm.store'), ['password' => 'password'])->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk();
        $this->from(route('auth.two-factor-setup'))->post(route('two-factor.enable'))->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee('data:image/svg+xml', false);
        $this->get(route('member.profile.edit'))->assertOk()->assertSee(__('two-factor.pending'));
        $this->get(route('member.dashboard'))->assertOk();
        $this->from(route('auth.two-factor-setup'))->post(route('two-factor.confirm'), ['code' => 'invalid'])
            ->assertSessionHasErrors(['code'], null, 'confirmTwoFactorAuthentication');
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee(__('The provided two factor authentication code was invalid.'));
        $this->post(route('two-factor.confirm'), ['code' => $this->code($user)])->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee($user->fresh()->recoveryCodes()[0]);
        $this->get(route('auth.two-factor-complete'))->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_unconfirmed_optional_setup_does_not_require_a_login_challenge(): void
    {
        $user = $this->with2FA($this->createMember());
        $user->forceFill(['two_factor_confirmed_at' => null])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('member.dashboard'));
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_existing_2fa_requires_challenge_and_cannot_be_reset_using_password_alone(): void
    {
        $user = $this->with2FA($this->createAdmin());
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->get(route('auth.two-factor-setup'))->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertOk();
        $this->post(route('two-factor.confirm'), ['code' => '123456'])->assertRedirect(route('login'));
        $this->post(route('two-factor.login.store'), ['code' => 'invalid'])->assertSessionHasErrors();
        $this->assertGuest();
        $this->post(route('two-factor.login.store'), ['code' => $this->code($user)])->assertRedirect(route('member.dashboard'));
        $this->get('/admin')->assertOk();
    }

    public function test_recovery_codes_are_single_use_and_can_be_regenerated(): void
    {
        $user = $this->with2FA($this->createMember());
        $oldCode = $user->recoveryCodes()[0];
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('two-factor.login'));
        $this->post(route('two-factor.login.store'), ['recovery_code' => $oldCode])->assertRedirect(route('member.dashboard'));
        $this->assertNotContains($oldCode, $user->fresh()->recoveryCodes());
        $this->get(route('auth.two-factor-setup'))->assertOk();
        $before = $user->fresh()->recoveryCodes();
        $this->from(route('auth.two-factor-setup'))->post(route('two-factor.regenerate-recovery-codes'))->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertOk()->assertSee($user->fresh()->recoveryCodes()[0]);
        $this->assertNotEquals($before, $user->fresh()->recoveryCodes());
        $this->post(route('logout'));
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login.store'), ['recovery_code' => $oldCode])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_optional_2fa_can_be_disabled_only_after_both_factors(): void
    {
        $user = $this->with2FA($this->createMember());
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->delete(route('two-factor.disable'))->assertRedirect(route('two-factor.login'));
        $this->assertTrue($user->fresh()->hasEnabledTwoFactorAuthentication());
        $this->post(route('two-factor.login.store'), ['code' => $this->code($user)]);
        $this->from(route('member.profile.edit'))->delete(route('two-factor.disable'))->assertRedirect(route('member.profile.edit'));
        $this->assertFalse($user->fresh()->hasEnabledTwoFactorAuthentication());
        $this->get(route('member.dashboard'))->assertOk();
    }

    public function test_screenshot_query_and_header_do_not_bypass_2fa(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user)->get('/admin?screenshot=1')->assertRedirect(route('auth.two-factor-setup'));
        $this->withHeader('X-Screenshot-Mode', '1')->get(route('member.dashboard'))->assertRedirect(route('auth.two-factor-setup'));
        $this->with2FA($user);
        $this->get('/admin?screenshot=1')->assertRedirect(route('two-factor.login'));
    }

    public function test_challenge_cannot_log_in_an_account_disabled_or_reset_after_password_verification(): void
    {
        $user = $this->with2FA($this->createAdmin());
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $user->update(['is_active' => false]);
        $this->post(route('two-factor.login.store'), ['code' => $this->code($user)])->assertRedirect(route('login'));
        $this->assertGuest();
        $user->forceFill(['is_active' => true, 'two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();
        $this->withSession(['login.id' => $user->id])->get(route('two-factor.login'))->assertRedirect(route('login'));
    }

    public function test_password_confirmation_preserves_the_final_admin_destination(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user)->get('/admin')->assertRedirect(route('auth.two-factor-setup'));
        $this->get(route('auth.two-factor-setup'))->assertRedirect(route('password.confirm'));
        $this->post(route('password.confirm.store'), ['password' => 'password'])->assertRedirect(route('auth.two-factor-setup'));
        $this->from(route('auth.two-factor-setup'))->post(route('two-factor.enable'));
        $this->post(route('two-factor.confirm'), ['code' => $this->code($user)]);
        $this->get(route('auth.two-factor-setup'))->assertOk();
        $this->get(route('auth.two-factor-complete'))->assertRedirect('/admin');
    }

    public function test_password_reset_of_a_member_with_2fa_still_requires_the_second_factor(): void
    {
        $user = $this->with2FA($this->createMember());
        $token = Password::createToken($user);
        $this->post('/reset-password', [
            'email' => $user->email, 'token' => $token,
            'password' => 'NewPassword123456!', 'password_confirmation' => 'NewPassword123456!',
        ])->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
        $this->post(route('two-factor.login.store'), ['code' => $this->code($user)])->assertRedirect(route('member.dashboard'));
    }

    public function test_logout_cancels_setup_and_pending_challenge(): void
    {
        $user = $this->createAdmin();
        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('logout.success'));
        $this->assertGuest();
        $this->with2FA($user);
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('logout'))->assertRedirect(route('logout.success'))->assertSessionMissing('login.id');
        $this->get(route('two-factor.login'))->assertRedirect(route('login'));
    }

    public function test_expired_or_changed_second_factor_invalidates_session_proof(): void
    {
        $user = $this->with2FA($this->createMember());
        $this->actingAs($user);
        $this->confirm2FA($user);
        $this->withSession(['auth.2fa_confirmed_at' => now()->subSeconds(config('auth.2fa_timeout') + 1)->timestamp]);
        $this->get(route('member.dashboard'))->assertRedirect(route('two-factor.login'));
        $this->actingAs($user);
        $this->confirm2FA($user);
        $user->forceFill(['two_factor_secret' => encrypt('JBSWY3DPEHPK3PXQ')])->save();
        $this->get(route('member.dashboard'))->assertRedirect(route('two-factor.login'));
    }

    public function test_remembered_device_requires_unexpired_proof_bound_to_account_password_and_secret(): void
    {
        $user = $this->with2FA($this->createMember());
        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response = $this->post(route('two-factor.login.store'), ['code' => $this->code($user), 'remember_device' => true]);
        $response->assertCookie('2fa_remember');
        $proof = $response->getCookie('2fa_remember')->getValue();
        $this->post(route('logout'));
        $this->withCookie('2fa_remember', $proof);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('member.dashboard'));
        $this->post(route('logout'));
        $user->forceFill(['password' => Hash::make('changed-password')])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'changed-password'])->assertRedirect(route('two-factor.login'));
        $this->assertGuest();
    }

    public function test_old_and_expired_remember_cookies_are_rejected(): void
    {
        $user = $this->with2FA($this->createMember());
        foreach ([['user_id' => $user->id, 'token' => 'legacy'], [
            'user_id' => $user->id, 'fingerprint' => app(TwoFactorService::class)->fingerprint($user), 'expires_at' => time() - 1,
        ]] as $data) {
            $this->actingAs($user)->withCookie('2fa_remember', json_encode($data));
            $this->get(route('member.dashboard'))->assertRedirect(route('two-factor.login'));
        }
    }

    public function test_redirects_reject_external_and_auth_destinations(): void
    {
        $user = $this->createMember();
        foreach (['//evil.example', '/\\evil.example', 'javascript://localhost/path', route('password.confirm'), url('/user/confirmed-two-factor-authentication')] as $url) {
            session(['url.intended' => $url]);
            $this->assertEquals('/clenska-sekce/dashboard', AuthRedirect::getTargetUrl($user));
        }
    }
}
