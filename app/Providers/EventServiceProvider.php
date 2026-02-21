<?php

namespace App\Providers;

use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Login;
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
