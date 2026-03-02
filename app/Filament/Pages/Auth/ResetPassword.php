<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Notifications\Notification;

class ResetPassword extends BaseResetPassword
{
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null; // Don't send notification, handle via exception
    }

    protected function getRateLimitedException(TooManyRequestsException $exception): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.email' => __('auth.throttle', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]),
        ]);
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
