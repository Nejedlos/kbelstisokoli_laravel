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
                "<style>
                    {!! app(\\App\\Services\\BrandingService::class)->getCssVariables() !!}
                    /* Stabilizace ikon pro zamezení FOUC (problikávání velkých glyfů) */
                    .fa-light, .fa-regular, .fa-solid, .fa-brands, .fa-thin, .fa-duotone, .fal, .far, .fas, .fab, .fat, .fad {
                        display: inline-block;
                        width: 1.25em;
                        height: 1em;
                        line-height: 1;
                        vertical-align: -0.125em;
                        overflow: hidden;
                        opacity: 0;
                    }
                 </style>
                 @vite(['resources/css/filament-admin.css'])
                 <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css' integrity='sha512-hvNR0F/e2J7zPPfLC9auFe3/SE0yG4aJCOd/qxew74NN7eyiSKjr7xJJMu1Jy2wf7FXITpWS1E/RY8yzuXN7VA==' crossorigin='anonymous' referrerpolicy='no-referrer' />
                 <script src='https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js' integrity='sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3FgvS56wzmyfMUHbQ4Km2b7l9+Y/+Eg==' crossorigin='anonymous' referrerpolicy='no-referrer'></script>"
            ))
            ->renderHook('panels::body.start', fn (): string => Blade::render('
                <x-impersonation-banner />
            '))
            ->renderHook('panels::body.end', fn (): string => Blade::render('
                <x-back-to-top />
                <livewire:member.avatar-modal />
            '))
            ->renderHook('panels::global-search.before', fn (): string => Blade::render('
                <div class="flex items-center gap-2 mr-3">
                    @include("filament.components.language-switch")
                    @include("filament.components.standard-search")
                </div>
            '))
            ->renderHook('panels::global-search.after', fn (): string => Blade::render('
                <div class="flex items-center gap-2 ml-2">
                    @include("filament.components.ai-search")
                    @include("filament.components.impersonate-select")
                </div>
            '))
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->brandName($branding['club_name'])
            ->brandLogo($branding['logo_path'] ? web_asset($branding['logo_path']) : null)
            ->favicon($branding['logo_path'] ? web_asset($branding['logo_path']) : asset('favicon.ico'))
            ->font('Instrument Sans')
            ->userMenuItems([
                'member_section' => MenuItem::make()
                    ->label(fn () => __('admin.navigation.pages.member_section'))
                    ->url(fn() => route('member.dashboard'))
                    ->icon(new HtmlString('<i class="fa-light fa-users-viewfinder fa-fw"></i>')),
                'public_web' => MenuItem::make()
                    ->label(fn () => __('admin.navigation.pages.public_web'))
                    ->url(fn() => route('public.home'))
                    ->icon(new HtmlString('<i class="fa-light fa-globe fa-fw"></i>')),
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
                // Widgets are now integrated into the custom Dashboard page view
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
