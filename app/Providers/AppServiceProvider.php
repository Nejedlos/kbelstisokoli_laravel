<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\HtmlString;

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
        \Filament\Support\Facades\FilamentIcon::register([
            'fal_announcement' => new HtmlString('<i class="fa-light fa-bullhorn fa-fw"></i>'),
            'fal_basketball' => new HtmlString('<i class="fa-light fa-basketball fa-fw"></i>'),
            'fal_basketball_hoop' => new HtmlString('<i class="fa-light fa-basketball-hoop fa-fw"></i>'),
            'fal_trophy' => new HtmlString('<i class="fa-light fa-trophy fa-fw"></i>'),
            'fal_calendar_star' => new HtmlString('<i class="fa-light fa-calendar-star fa-fw"></i>'),
            'fal_history' => new HtmlString('<i class="fa-light fa-history fa-fw"></i>'),
            'fal_clock' => new HtmlString('<i class="fa-light fa-clock fa-fw"></i>'),
            'fal_cloud_arrow_down' => new HtmlString('<i class="fa-light fa-cloud-arrow-down fa-fw"></i>'),
            'fal_file_invoice_dollar' => new HtmlString('<i class="fa-light fa-file-invoice-dollar fa-fw"></i>'),
            'fal_money_bill_transfer' => new HtmlString('<i class="fa-light fa-money-bill-transfer fa-fw"></i>'),
            'fal_film' => new HtmlString('<i class="fa-light fa-film fa-fw"></i>'),
            'fal_images' => new HtmlString('<i class="fa-light fa-images fa-fw"></i>'),
            'fal_bars' => new HtmlString('<i class="fa-light fa-bars fa-fw"></i>'),
            'fal_shield' => new HtmlString('<i class="fa-light fa-shield fa-fw"></i>'),
            'fal_file_lines' => new HtmlString('<i class="fa-light fa-file-lines fa-fw"></i>'),
            'fal_key' => new HtmlString('<i class="fa-light fa-key fa-fw"></i>'),
            'fal_id_card' => new HtmlString('<i class="fa-light fa-id-card fa-fw"></i>'),
            'fal_tags' => new HtmlString('<i class="fa-light fa-tags fa-fw"></i>'),
            'fal_newspaper' => new HtmlString('<i class="fa-light fa-newspaper fa-fw"></i>'),
            'fal_shuffle' => new HtmlString('<i class="fa-light fa-shuffle fa-fw"></i>'),
            'fal_user_shield' => new HtmlString('<i class="fa-light fa-user-shield fa-fw"></i>'),
            'fal_calendar_days' => new HtmlString('<i class="fa-light fa-calendar-days fa-fw"></i>'),
            'fal_chart_column' => new HtmlString('<i class="fa-light fa-chart-column fa-fw"></i>'),
            'fal_user_group' => new HtmlString('<i class="fa-light fa-user-group fa-fw"></i>'),
            'fal_users' => new HtmlString('<i class="fa-light fa-users fa-fw"></i>'),
            'fal_palette' => new HtmlString('<i class="fa-light fa-palette fa-fw"></i>'),
            'fal_clipboard_list' => new HtmlString('<i class="fa-light fa-clipboard-list fa-fw"></i>'),
        ]);

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
