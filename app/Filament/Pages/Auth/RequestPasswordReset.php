<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Notifications\Notification;

class RequestPasswordReset extends BaseRequestPasswordReset
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

    // Override layout to use our custom full auth layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.request-password-reset';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Zapomenuté heslo');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Stává se i nejlepším střelcům. Pošleme přihrávku na nový start.');
    }

    public function getIcon(): string
    {
        return 'fa-key';
    }
}
