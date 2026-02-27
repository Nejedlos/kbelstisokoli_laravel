<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
            return new \App\Services\AuditLogService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->visible(false);
        });

        \Illuminate\Support\Facades\View::composer(['layouts.*', 'public.*', 'member.*', 'auth.*', 'errors.*'], function ($view) {
            // Statická cache pro minimalizaci DB dotazů v rámci jednoho requestu
            static $cachedData = null;

            $brandingService = app(\App\Services\BrandingService::class);
            $communicationService = app(\App\Services\Communication\CommunicationService::class);

            $viewName = $view->getName();
            $audience = str_contains($viewName, 'member') ? 'member' : 'public';

            if ($cachedData === null) {
                $branding = $brandingService->getSettings();
                $branding['club_name'] = $brandingService->replacePlaceholders($branding['club_name']);
                $branding['club_short_name'] = $brandingService->replacePlaceholders($branding['club_short_name']);
                $branding['slogan'] = $brandingService->replacePlaceholders($branding['slogan'] ?? '');

                $cachedData = [
                    'branding' => $branding,
                    'branding_css' => $brandingService->getCssVariables(),
                    'announcements_public' => $communicationService->getActiveAnnouncements('public'),
                    'announcements_member' => $communicationService->getActiveAnnouncements('member'),
                    'footerMenu' => \App\Models\Menu::where('location', 'footer')->with('items')->first()?->items ?? collect(),
                    'footerClubMenu' => \App\Models\Menu::where('location', 'footer_club')->with('items')->first()?->items ?? collect(),
                ];
            }

            $view->with('branding', $cachedData['branding']);
            $view->with('branding_css', $cachedData['branding_css']);
            $view->with('announcements', $cachedData["announcements_{$audience}"]);

            // Menus for Public Footer
            if ($audience === 'public') {
                $view->with('footerMenu', $cachedData['footerMenu']);
                $view->with('footerClubMenu', $cachedData['footerClubMenu']);
            }

            // Přidání SEO metadat pro public layout, pokud už nejsou nastaveny
            if ($audience === 'public' && ! isset($view->seo)) {
                $seoService = app(\App\Services\SeoService::class);
                $model = $view->page ?? $view->post ?? $view->news ?? $view->team ?? $view->gallery ?? $view->pool ?? null;
                $view->with('seo', $seoService->getMetadata($model));
            }

            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unreadNotifications->count());
            }
        });
    }
}
