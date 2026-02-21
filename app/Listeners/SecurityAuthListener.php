<?php

namespace App\Listeners;

use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

class SecurityAuthListener
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            SecurityLogger::log('login_success', ['email' => $event->user->email]);
        } elseif ($event instanceof Failed) {
            SecurityLogger::log('login_failed', ['email' => $event->credentials['email'] ?? 'unknown']);
        } elseif ($event instanceof Logout) {
            SecurityLogger::log('logout');
        } elseif ($event instanceof PasswordReset) {
            SecurityLogger::log('password_reset', ['email' => $event->user->email]);
        } elseif ($event instanceof TwoFactorAuthenticationEnabled) {
            SecurityLogger::log('2fa_enabled', ['user_id' => $event->user->id]);
        } elseif ($event instanceof TwoFactorAuthenticationDisabled) {
            SecurityLogger::log('2fa_disabled', ['user_id' => $event->user->id]);
        }
    }
}
