<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('auth.throttle', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();

        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword $user, string $token): void {
                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;

                    throw new \LogicException("Model [{$userClass}] does not have a [notify()] method.");
                }

                $notification = app(\Filament\Auth\Notifications\ResetPassword::class, ['token' => $token]);
                $notification->url = Filament::getResetPasswordUrl($token, $user);

                $user->notify($notification);

                if (class_exists(PasswordResetLinkSent::class)) {
                    event(new PasswordResetLinkSent($user));
                }
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'data.email' => [__($status)],
            ]);
        }

        session()->flash('status', __($status));

        $this->form->fill();
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null;
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
