<?php

namespace App\Listeners\InternalAnalytics;

use App\Services\InternalAnalytics\InternalAnalyticsService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Request;

class AuthEventListener
{
    public function __construct(
        protected InternalAnalyticsService $analyticsService
    ) {}

    public function handleLogin(Login $event): void
    {
        $this->analyticsService->trackEvent('login_success', [
            'user_id' => $event->user->id,
            'guard' => $event->guard,
            'area' => $this->analyticsService->resolveArea(request()),
            'path' => request()->path(),
            'ip_hash' => $this->analyticsService->makeIpHash(request()),
            'visitor_hash' => $this->analyticsService->makeVisitorHash(request()),
        ]);
    }

    public function handleLogout(Logout $event): void
    {
        $this->analyticsService->trackEvent('logout', [
            'user_id' => $event->user?->id,
            'guard' => $event->guard,
            'area' => $this->analyticsService->resolveArea(request()),
            'path' => request()->path(),
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        $this->analyticsService->trackEvent('login_failed', [
            'guard' => $event->guard,
            'area' => $this->analyticsService->resolveArea(request()),
            'path' => request()->path(),
            'metadata' => [
                'credentials' => array_keys($event->credentials),
            ],
            'ip_hash' => $this->analyticsService->makeIpHash(request()),
            'visitor_hash' => $this->analyticsService->makeVisitorHash(request()),
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->analyticsService->trackEvent('password_reset_completed', [
            'user_id' => $event->user->id,
            'area' => $this->analyticsService->resolveArea(request()),
            'path' => request()->path(),
        ]);
    }

    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
