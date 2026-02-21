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
            $seoService = app(\App\Services\SeoService::class);
            $communicationService = app(\App\Services\Communication\CommunicationService::class);

            $audience = str_contains($view->getName(), 'member') ? 'member' : 'public';

            $view->with('branding', $brandingService->getSettings());
            $view->with('branding_css', $brandingService->getCssVariables());
            $view->with('announcements', $communicationService->getActiveAnnouncements($audience));

            // Přidání SEO metadat pro public layout, pokud už nejsou nastaveny
            if ($audience === 'public' && !isset($view->seo)) {
                $model = $view->page ?? $view->post ?? $view->news ?? null;
                $view->with('seo', $seoService->getMetadata($model));
            }

            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications->count());
            }
        });
    }
}
