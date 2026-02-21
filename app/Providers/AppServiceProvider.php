<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;

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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(config('app.supported_locales', ['cs', 'en']))
                ->visible(insidePanels: true, outsidePanels: true)
                ->renderHook('panels::global-search.after')
                ->outsidePanelPlacement(\BezhanSalleh\LanguageSwitch\Enums\Placement::TopRight);
        });

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
