<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use App\Listeners\SecurityAuthListener;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            UpdateLastLoginAt::class,
            SecurityAuthListener::class,
        ],
        Failed::class => [
            SecurityAuthListener::class,
        ],
        Logout::class => [
            SecurityAuthListener::class,
        ],
        PasswordReset::class => [
            SecurityAuthListener::class,
        ],
        TwoFactorAuthenticationEnabled::class => [
            SecurityAuthListener::class,
        ],
        TwoFactorAuthenticationDisabled::class => [
            SecurityAuthListener::class,
        ],
        \App\Events\RsvpChanged::class => [
            \App\Listeners\SendRsvpNotification::class,
        ],
        \App\Events\FinanceChargeCreated::class => [
            \App\Listeners\SendFinanceNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // You may also register events manually using Event::listen(...)
    }
}
