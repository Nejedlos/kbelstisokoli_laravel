<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer(['layouts.public', 'layouts.member'], function ($view) {
            $brandingService = app(\App\Services\BrandingService::class);
            $communicationService = app(\App\Services\Communication\CommunicationService::class);

            $audience = str_contains($view->getName(), 'member') ? 'member' : 'public';

            $view->with('branding', $brandingService->getSettings());
            $view->with('branding_css', $brandingService->getCssVariables());
            $view->with('announcements', $communicationService->getActiveAnnouncements($audience));

            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications->count());
            }
        });
    }
}
