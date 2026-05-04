<?php

namespace App\Listeners;

use App\Services\AuditLogService;
use App\Services\SecurityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;

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
            $this->auditLogService->security(
                eventKey: 'login_failed',
                action: 'login_failed',
                metadata: [
                    'email' => $event->credentials['email'] ?? 'unknown',
                    'credentials_keys' => array_keys($event->credentials),
                ],
                severity: 'warning',
                actor: $event->user
            );
            SecurityLogger::log('login_failed', ['email' => $event->credentials['email'] ?? 'unknown']);
        } elseif ($event instanceof Logout) {
            $this->auditLogService->security('logout', 'logout');
            SecurityLogger::log('logout');
        } elseif ($event instanceof PasswordReset) {
            $this->auditLogService->security(
                eventKey: 'password_reset',
                action: 'password_reset',
                metadata: ['email' => $event->user->email],
                actor: $event->user
            );
            SecurityLogger::log('password_reset', ['email' => $event->user->email]);
        } elseif ($event instanceof TwoFactorAuthenticationEnabled) {
            $this->auditLogService->security(
                eventKey: '2fa_enabled',
                action: '2fa_enabled',
                metadata: ['user_id' => $event->user->id],
                actor: $event->user
            );
            SecurityLogger::log('2fa_enabled', ['user_id' => $event->user->id]);
        } elseif ($event instanceof TwoFactorAuthenticationDisabled) {
            $this->auditLogService->security(
                eventKey: '2fa_disabled',
                action: '2fa_disabled',
                metadata: ['user_id' => $event->user->id],
                severity: 'warning',
                actor: $event->user
            );
            SecurityLogger::log('2fa_disabled', ['user_id' => $event->user->id]);
        }
    }
}
