<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Filament\Notifications\Notification;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
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

    // Override Filament's simple layout with our custom auth layout
    protected static string $layout = 'filament.admin.layouts.auth';

    protected string $view = 'filament.admin.auth.email-verification-prompt';

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Ověření e-mailu');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Ještě jeden krok před výkopem. Potvrďte svůj e-mail.');
    }

    public function getIcon(): string
    {
        return 'fa-envelope-dot';
    }
}
