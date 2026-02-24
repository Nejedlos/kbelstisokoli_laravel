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
                "<style>{!! app(\\App\\Services\\BrandingService::class)->getCssVariables() !!}</style>
                 @vite(['resources/css/filament-admin.css'])"
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::USERS_GROUP)),
                MenuItem::make()
                    ->label(fn (): string => __('admin.navigation.pages.public_web'))
                    ->url(fn (): string => route('public.home'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::GLOBE)),
            ])
            ->colors([
                'primary' => Color::hex($colors['red']),
                'gray' => Color::Slate,
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
