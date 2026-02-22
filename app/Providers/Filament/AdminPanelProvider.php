<?php

namespace App\Providers\Filament;

use App\Services\BrandingService;
use Illuminate\Support\Facades\Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\Auth\ResetPassword;
use App\Filament\Pages\Auth\EmailVerificationPrompt;

class AdminPanelProvider extends PanelProvider
{
    // Pozn.: Hooky a assety registrujeme přímo na panelu v metodě panel(),
    // aby se spolehlivě vykreslily i na auth stránkách.

    public function panel(Panel $panel): Panel
    {
        $branding = app(BrandingService::class)->getSettings();
        $colors = $branding['colors'];

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Vložíme vlastní CSS variables do <head> přes render hook (globálně pro barvy)
            ->renderHook('panels::head.end', fn (): string => Blade::render(
                "<style>{!! app(\\App\\Services\\BrandingService::class)->getCssVariables() !!}</style>"
            ))
            ->renderHook('panels::global-search.before', fn (): string => view('filament.components.ai-search'))
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->brandName($branding['club_name'])
            ->brandLogo($branding['logo_path'] ? asset('storage/' . $branding['logo_path']) : null)
            ->favicon($branding['logo_path'] ? asset('storage/' . $branding['logo_path']) : asset('favicon.ico'))
            ->font('Instrument Sans')
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => __('admin.navigation.pages.member_section'))
                    ->url(fn (): string => route('member.dashboard'))
                    ->icon(new HtmlString('<i class="fa-light fa-user-group fa-fw"></i>')),
                MenuItem::make()
                    ->label(fn (): string => __('admin.navigation.pages.public_web'))
                    ->url(fn (): string => route('public.home'))
                    ->icon(new HtmlString('<i class="fa-light fa-globe fa-fw"></i>')),
            ])
            ->colors([
                'primary' => Color::hex($colors['red']),
                'gray' => Color::Slate,
            ])
            ->icons([
                'fa-light-users' => new HtmlString('<i class="fa-light fa-users fa-fw"></i>'),
                'fa-light-clipboard-list' => new HtmlString('<i class="fa-light fa-clipboard-list fa-fw"></i>'),
                'fa-light-shuffle' => new HtmlString('<i class="fa-light fa-shuffle fa-fw"></i>'),
                'fa-light-clock' => new HtmlString('<i class="fa-light fa-clock fa-fw"></i>'),
                'fa-light-history' => new HtmlString('<i class="fa-light fa-history fa-fw"></i>'),
                'fa-light-palette' => new HtmlString('<i class="fa-light fa-palette fa-fw"></i>'),
                'fa-light-images' => new HtmlString('<i class="fa-light fa-images fa-fw"></i>'),
                'fa-light-newspaper' => new HtmlString('<i class="fa-light fa-newspaper fa-fw"></i>'),
                'fa-light-tags' => new HtmlString('<i class="fa-light fa-tags fa-fw"></i>'),
                'fa-light-file-lines' => new HtmlString('<i class="fa-light fa-file-lines fa-fw"></i>'),
                'fa-light-bars' => new HtmlString('<i class="fa-light fa-bars fa-fw"></i>'),
                'fa-light-money-bill-transfer' => new HtmlString('<i class="fa-light fa-money-bill-transfer fa-fw"></i>'),
                'fa-light-file-invoice-dollar' => new HtmlString('<i class="fa-light fa-file-invoice-dollar fa-fw"></i>'),
                'fa-light-basketball' => new HtmlString('<i class="fa-light fa-basketball fa-fw"></i>'),
                'fa-light-basketball-hoop' => new HtmlString('<i class="fa-light fa-basketball-hoop fa-fw"></i>'),
                'fa-light-trophy' => new HtmlString('<i class="fa-light fa-trophy fa-fw"></i>'),
                'fa-light-shield' => new HtmlString('<i class="fa-light fa-shield fa-fw"></i>'),
                'fa-light-chart-column' => new HtmlString('<i class="fa-light fa-chart-column fa-fw"></i>'),
                'fa-light-cloud-arrow-down' => new HtmlString('<i class="fa-light fa-cloud-arrow-down fa-fw"></i>'),
                'fa-light-bullhorn' => new HtmlString('<i class="fa-light fa-bullhorn fa-fw"></i>'),
                'fa-light-user-shield' => new HtmlString('<i class="fa-light fa-user-shield fa-fw"></i>'),
                'fa-light-key' => new HtmlString('<i class="fa-light fa-key fa-fw"></i>'),
                'fa-light-id-card' => new HtmlString('<i class="fa-light fa-id-card fa-fw"></i>'),
                'fa-light-calendar-days' => new HtmlString('<i class="fa-light fa-calendar-days fa-fw"></i>'),
                'fa-light-user-group' => new HtmlString('<i class="fa-light fa-user-group fa-fw"></i>'),
                'fa-light-calendar-star' => new HtmlString('<i class="fa-light fa-calendar-star fa-fw"></i>'),
                'fa-light-film' => new HtmlString('<i class="fa-light fa-film fa-fw"></i>'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                     ->label(fn (): string => __('admin.navigation.groups.sports_agenda')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.communication')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.user_management')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.statistics')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.content')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.media')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.finance')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.admin_tools')),
            ])
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\AdminKpiOverview::class,
                \App\Filament\Widgets\FinanceOverview::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SetLocaleMiddleware::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                '2fa.required',
                '2fa.timeout',
            ]);
    }
}
