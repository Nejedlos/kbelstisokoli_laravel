<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    public function resendNotificationAction(): Action
    {
        return Action::make('resendNotification')
            ->link()
            ->label(__('filament-panels::auth/pages/email-verification/email-verification-prompt.actions.resend_notification.label') . '.')
            ->size('sm')
            ->action(function (): void {
                try {
                    $this->rateLimit(2);
                } catch (TooManyRequestsException $exception) {
                    throw ValidationException::withMessages([
                        'resend' => __('auth.throttle', [
                            'seconds' => $exception->secondsUntilAvailable,
                            'minutes' => ceil($exception->secondsUntilAvailable / 60),
                        ]),
                    ]);
                }

                $this->sendEmailVerificationNotification($this->getVerifiable());

                session()->flash('status', __('filament-panels::auth/pages/email-verification/email-verification-prompt.notifications.notification_resent.title'));
            });
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null;
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
