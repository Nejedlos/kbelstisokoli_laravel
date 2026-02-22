<?php

namespace App\Providers\Filament;

use App\Services\BrandingService;
use Illuminate\Support\Facades\Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            // Vložíme vlastní assety a CSS variables do <head> přes render hook
            ->renderHook('panels::head.end', fn (): string => Blade::render(
                "<style>{!! app(\\App\\Services\\BrandingService::class)->getCssVariables() !!}</style>" .
                "@vite(['resources/css/filament-auth.css', 'resources/js/filament-auth.js', 'resources/js/filament-error-handler.js'])"
            ))
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->brandName($branding['club_name'])
            ->brandLogo($branding['logo_path'] ? asset('storage/' . $branding['logo_path']) : null)
            ->favicon($branding['logo_path'] ? asset('storage/' . $branding['logo_path']) : asset('favicon.ico'))
            ->font('Instrument Sans')
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Členská sekce')
                    ->url(fn (): string => route('member.dashboard'))
                    ->icon('heroicon-o-user-group'),
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
            ]);
    }
}
