<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // Optimalizace pro localhost: vypneme náročné sledovače, které nepotřebujeme neustále
        if ($isLocal) {
            config([
                'telescope.watchers.' . \Laravel\Telescope\Watchers\ModelWatcher::class . '.hydrations' => false,
                'telescope.watchers.' . \Laravel\Telescope\Watchers\QueryWatcher::class . '.slow' => 50, // Zaznamenáváme jen dotazy nad 50ms
                'telescope.watchers.' . \Laravel\Telescope\Watchers\ViewWatcher::class => false, // ViewWatcher je na localhostu velmi hlučný
            ]);
        }

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            if ($isLocal) {
                // Na localhostu filtrujeme Livewire polling a tiché requesty, pokud nejsou chybové
                if ($entry->type === 'request' && (
                    str_contains($entry->content['uri'] ?? '', 'livewire/update') ||
                    str_contains($entry->content['uri'] ?? '', '_debugbar') ||
                    str_contains($entry->content['uri'] ?? '', 'telescope')
                )) {
                    return $entry->isReportableException() || $entry->isFailedRequest();
                }

                // Pokud jde o query, na localhostu nás zajímají jen ty pomalé (config výše zajistí 'slow' flag)
                if ($entry->type === 'query') {
                    return ($entry->content['slow'] ?? false) || $entry->isReportableException();
                }

                return true;
            }

            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
