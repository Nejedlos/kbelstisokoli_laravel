<?php

namespace App\Providers;

use App\Listeners\SecurityAuthListener;
use App\Listeners\UpdateLastLoginAt;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;

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
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \App\Listeners\InternalAnalytics\AuthEventListener::class,
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSending::class,
            function ($event) {
                $to = array_map(fn($address) => $address->toString(), $event->message->getTo());
                $cc = array_map(fn($address) => $address->toString(), $event->message->getCc());
                $bcc = array_map(fn($address) => $address->toString(), $event->message->getBcc());

                \Illuminate\Support\Facades\Log::warning('DEBUG_MAIL: Sending email', [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.'.config('mail.default').'.host') ?? 'n/a',
                    'to' => $to,
                    'cc' => $cc,
                    'bcc' => $bcc,
                    'subject' => $event->message->getSubject(),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Mail\Events\MessageSent::class,
            function ($event) {
                $to = array_map(fn($address) => $address->toString(), $event->message->getTo());

                \Illuminate\Support\Facades\Log::warning('DEBUG_MAIL: Email sent successfully', [
                    'to' => $to,
                    'subject' => $event->message->getSubject(),
                    'sent_at' => now()->toDateTimeString(),
                ]);
            }
        );
    }
}
