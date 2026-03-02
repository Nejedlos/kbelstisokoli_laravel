<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Notifications\Notification;

class Register extends BaseRegister
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

    // Use custom full-page auth layout instead of Filament's simple layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.register';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Registrace nového hráče');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Staň se součástí týmu a vyběhni na palubovku.');
    }

    public function getIcon(): string
    {
        return 'fa-user-plus';
    }
}
