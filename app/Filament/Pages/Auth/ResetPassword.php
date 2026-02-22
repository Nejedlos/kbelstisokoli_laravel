<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;

use Filament\Notifications\Notification;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class ResetPassword extends BaseResetPassword
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
