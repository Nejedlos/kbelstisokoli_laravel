<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Notifications\Notification;

class Register extends BaseRegister
{
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('Too many login attempts. Please try again in :seconds seconds.', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->danger();
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
