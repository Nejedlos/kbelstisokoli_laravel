<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ProductionValidationFeedbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Exercise the production exception renderer with the in-memory test DB.
        $this->app->instance('env', 'production');
        $this->withoutMiddleware(PreventRequestForgery::class);
        Mail::fake();
    }

    public function test_rejected_reset_returns_to_form_with_visible_error_and_keeps_password_and_token(): void
    {
        $this->mock(UncompromisedVerifier::class)
            ->shouldReceive('verify')->once()->andReturnFalse();

        $user = User::factory()->create();
        $originalHash = $user->password;
        $token = Password::createToken($user);
        $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $response = $this->from($url)->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'RejectedExample123',
            'password_confirmation' => 'RejectedExample123',
        ]);

        $response->assertRedirect($url)->assertSessionHasErrors('password');
        $this->assertSame($originalHash, $user->fresh()->password);
        $this->assertTrue(Password::tokenExists($user, $token));
        $this->assertArrayNotHasKey('password', session()->getOldInput());
        $this->assertArrayNotHasKey('password_confirmation', session()->getOldInput());

        $error = session('errors')->first('password');
        $this->get($url)->assertOk()
            ->assertSee($error)
            ->assertSee(__('passwords.not_changed'))
            ->assertSee(__('passwords.requirements'))
            ->assertSee('id="password-error"', false)
            ->assertDontSee('RejectedExample123');

        Mail::assertNothingSent();
    }

    public function test_production_json_validation_returns_422_with_field_errors(): void
    {
        $this->postJson(route('password.update'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password', 'token'])
            ->assertJsonMissingPath('exception');

        Mail::assertNothingSent();
    }

    public function test_invalid_login_returns_validation_message_instead_of_server_error(): void
    {
        $this->from(route('login'))->post(route('login'), [
            'email' => 'missing-user@example.test',
            'password' => 'IncorrectExample123',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertArrayNotHasKey('password', session()->getOldInput());
        Mail::assertNothingSent();
    }

    public function test_guest_opening_two_factor_completion_is_redirected_to_login(): void
    {
        $this->get(route('auth.two-factor-complete'))
            ->assertRedirect(route('login'));

        Mail::assertNothingSent();
    }
}
