<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPassword extends BaseResetPassword
{
    public function resetPassword(): ?PasswordResetResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.password' => __('auth.throttle', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        $data['email'] = $this->email;
        $data['token'] = $this->token;

        $status = Password::broker(Filament::getAuthPasswordBroker())->reset(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword|Model|Authenticatable $user) use ($data): void {
                $user->forceFill([
                    $user->getAuthPasswordName() => Hash::make($data['password']),
                    $user->getRememberTokenName() => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __($status));

            return app(PasswordResetResponse::class);
        }

        throw ValidationException::withMessages([
            'data.password' => [__($status)],
        ]);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null;
    }

    // Use our custom auth layout for full control over the page shell
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.reset-password';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Nové heslo');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Resetujte si nastavení a naskočte zpět do hry.');
    }

    public function getIcon(): string
    {
        return 'fa-lock-open';
    }
}
