<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\Auth\FailedPasswordResetLinkRequestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use App\Filament\Pages\Auth\RequestPasswordReset;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear("pw-reset-ip:" . md5('127.0.0.1'));
    }

    public function test_filament_forgot_password_sends_email_for_existing_user()
    {
        Notification::fake();
        $email = 'admin-' . uniqid() . '@example.com';
        $user = User::factory()->create(['email' => $email]);

        Livewire::test(RequestPasswordReset::class)
            ->set('data.email', $email)
            ->call('request')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_filament_forgot_password_does_not_reveal_non_existing_user()
    {
        Notification::fake();
        $email = 'nonexisting-admin-' . uniqid() . '@example.com';

        Livewire::test(RequestPasswordReset::class)
            ->set('data.email', $email)
            ->call('request')
            ->assertHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_password_reset_integrity_2fa()
    {
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);

        app(ResetUserPassword::class)->reset($user, [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $user->refresh();
        $this->assertEquals('secret', $user->two_factor_secret);
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_fortify_failed_response_anti_enumeration()
    {
        $response = new FailedPasswordResetLinkRequestResponse(Password::INVALID_USER);

        // Pro JSON request by měl vrátit 200 a neutrální zprávu
        $request = request();
        $request->headers->set('Accept', 'application/json');

        $result = $response->toResponse($request);

        $this->assertEquals(200, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertEquals(__('passwords.sent'), $content['message']);
    }

    public function test_rate_limiting_logic()
    {
        $service = app(\App\Services\Auth\PasswordResetService::class);
        $email = 'throttle-' . uniqid() . '@example.com';
        $emailHash = md5($email);
        $ipHash = md5(request()->ip());

        RateLimiter::clear("pw-reset-email-throttle:{$emailHash}");
        RateLimiter::clear("pw-reset-email:{$emailHash}");
        RateLimiter::clear("pw-reset-ip:{$ipHash}");

        // První pokus - OK
        $status = $service->sendResetLink($email);
        $this->assertEquals(Password::INVALID_USER, $status);

        // Druhý pokus okamžitě - Throttled
        try {
            $service->sendResetLink($email);
            $this->fail('ValidationException was not thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertStringContainsString('Počkejte prosím', $e->errors()['email'][0]);
            $this->assertStringContainsString('sekund', $e->errors()['email'][0]);
        }
    }
}
