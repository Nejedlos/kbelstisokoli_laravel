<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

class SecurityAuthListener
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            $this->auditLogService->security('login_success', 'login', [
                'email' => $event->user->email,
                'guard' => $event->guard,
            ]);
            SecurityLogger::log('login_success', ['email' => $event->user->email]);
        } elseif ($event instanceof Failed) {
            $this->auditLogService->security('login_failed', 'login_failed', [
                'email' => $event->credentials['email'] ?? 'unknown',
                'credentials_keys' => array_keys($event->credentials),
            ], 'warning');
            SecurityLogger::log('login_failed', ['email' => $event->credentials['email'] ?? 'unknown']);
        } elseif ($event instanceof Logout) {
            $this->auditLogService->security('logout', 'logout');
            SecurityLogger::log('logout');
        } elseif ($event instanceof PasswordReset) {
            $this->auditLogService->security('password_reset', 'password_reset', [
                'email' => $event->user->email,
            ]);
            SecurityLogger::log('password_reset', ['email' => $event->user->email]);
        } elseif ($event instanceof TwoFactorAuthenticationEnabled) {
            $this->auditLogService->security('2fa_enabled', '2fa_enabled', [
                'user_id' => $event->user->id,
            ]);
            SecurityLogger::log('2fa_enabled', ['user_id' => $event->user->id]);
        } elseif ($event instanceof TwoFactorAuthenticationDisabled) {
            $this->auditLogService->security('2fa_disabled', '2fa_disabled', [
                'user_id' => $event->user->id,
            ], 'warning');
            SecurityLogger::log('2fa_disabled', ['user_id' => $event->user->id]);
        }
    }
}
