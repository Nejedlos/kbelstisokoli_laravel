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
        $this->app->bind(
            \Filament\Auth\Notifications\ResetPassword::class,
            \App\Notifications\Auth\ResetPasswordNotification::class
        );

        $this->app->bind(
            \Filament\Auth\Notifications\VerifyEmail::class,
            \App\Notifications\Auth\VerifyEmailNotification::class
        );

        $this->app->singleton(\App\Services\AuditLogService::class, function ($app) {
            return new \App\Services\AuditLogService();
        });
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

        \Illuminate\Support\Facades\View::composer(['layouts.*', 'public.*', 'member.*', 'auth.*', 'errors.*'], function ($view) {
            $brandingService = app(\App\Services\BrandingService::class);
            $seoService = app(\App\Services\SeoService::class);
            $communicationService = app(\App\Services\Communication\CommunicationService::class);

            $audience = str_contains($view->getName(), 'member') ? 'member' : 'public';

            // Branding and CSS
            $branding = $brandingService->getSettings();
            $branding['club_name'] = $brandingService->replacePlaceholders($branding['club_name']);
            $branding['club_short_name'] = $brandingService->replacePlaceholders($branding['club_short_name']);
            $branding['slogan'] = $brandingService->replacePlaceholders($branding['slogan'] ?? '');

            $view->with('branding', $branding);
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
