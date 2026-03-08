<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerificationPrompt;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\Auth\ResetPassword;
use App\Filament\Pages\Dashboard;
use App\Services\BrandingService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->darkMode(false)
            // Vložíme vlastní CSS variables do <head> přes render hook (globálně pro barvy)
            ->renderHook('panels::head.end', function (): string {
                // Podle toho, zda jsme na auth stránkách nebo v adminu, zvolíme správný CSS entrypoint
                $isAuth = request()->routeIs('filament.admin.auth.*');
                $entrypoints = $isAuth
                    ? ['resources/css/filament-auth.css']
                    : ['resources/css/filament-admin.css'];

                $cropper = '';
                if (auth()->check() && ! $isAuth) {
                    $cropper = "
                        <link rel='stylesheet' href='/assets/vendor/cropper.min.css' />
                        <script src='/assets/vendor/cropper.min.js'></script>
                    ";
                }

                // Branding service vrací --color-brand-* tokens
                $brandingService = app(\App\Services\BrandingService::class);
                $brandingVariables = $brandingService->getCssVariables();

                // Pokud jsme na auth stránce, přidáme aliasy --brand-* (pro filament-auth.css)
                $authAliases = '';
                if ($isAuth) {
                    $settings = $brandingService->getSettings();
                    $colors = $settings['colors'];
                    $hexToRgb = function ($hex) {
                        $hex = str_replace('#', '', (string) $hex);

                        return \App\Support\ColorHelper::hexToRgb($hex); // Použijeme helper pokud existuje, nebo inline
                    };
                    // Rychlý inline hexToRgb pro spolehlivost v provideru
                    $inlineHexToRgb = function ($hex) {
                        $hex = str_replace('#', '', (string) $hex);
                        if (strlen($hex) === 3) {
                            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
                            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
                            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
                        } else {
                            $r = hexdec(substr($hex, 0, 2));
                            $g = hexdec(substr($hex, 2, 2));
                            $b = hexdec(substr($hex, 4, 2));
                        }

                        return "{$r}, {$g}, {$b}";
                    };

                    $authAliases = '
                        :root {
                            --brand-navy: '.($colors['navy'] ?? '#0b1f3a').';
                            --brand-navy-rgb: '.$inlineHexToRgb($colors['navy'] ?? '#0b1f3a').';
                            --brand-blue: '.($colors['blue'] ?? '#2563eb').';
                            --brand-blue-rgb: '.$inlineHexToRgb($colors['blue'] ?? '#2563eb').';
                            --brand-red: '.($colors['red'] ?? '#e11d48').';
                            --brand-red-rgb: '.$inlineHexToRgb($colors['red'] ?? '#e11d48').';
                            --brand-red-hover: '.($colors['red_hover'] ?? '#be123c').';
                            --brand-white: #ffffff;

                            /* UI tokens pro auth */
                            --ui-text: rgba(255, 255, 255, 0.92);
                            --ui-text-muted: rgba(255, 255, 255, 0.65);
                            --ui-border: rgba(255, 255, 255, 0.18);
                            --ui-surface: rgba(255, 255, 255, 0.80);
                            --ui-surface-elevated: rgba(255, 255, 255, 0.90);
                            --ui-success: #22c55e;
                            --ui-danger: #ef4444;
                            --ui-warning: #f59e0b;
                        }
                    ';
                }

                return Blade::render(
                    "
                    <link rel='preconnect' href='https://fonts.googleapis.com'>
                    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
                    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Oswald:wght@200..700&display=swap'>
                    <style>
                        {!! \$brandingVariables !!}
                        {!! \$authAliases !!}
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
                     @vite(\$entrypoints)
                     @if (\$isAuth)
                        @vite(['resources/js/filament-auth.js', 'resources/js/filament-error-handler.js'])
                     @endif
                     {!! \$cropper !!}",
                    ['entrypoints' => $entrypoints, 'authAliases' => $authAliases, 'brandingVariables' => $brandingVariables, 'cropper' => $cropper, 'isAuth' => $isAuth]
                );
            })
            ->renderHook('panels::body.start', function (): string {
                if (! auth()->check() || request()->routeIs('filament.admin.auth.*')) {
                    return '';
                }

                return Blade::render('
                    <x-impersonation-banner />
                    <x-impersonation-notification />
                ');
            })
            ->renderHook('panels::body.end', function (): string {
                if (! auth()->check() || request()->routeIs('filament.admin.auth.*')) {
                    return '';
                }

                return Blade::render('
                    <x-back-to-top />
                    <livewire:member.avatar-modal />
                ');
            })
            ->renderHook('panels::global-search.before', function (): string {
                if (! auth()->check()) {
                    return '';
                }

                return Blade::render('
                    <div class="flex items-center gap-2 mr-3">
                        @include("filament.components.language-switch")
                        @include("filament.components.standard-search")
                    </div>
                ');
            })
            ->renderHook('panels::global-search.after', function (): string {
                if (! auth()->check()) {
                    return '';
                }

                return Blade::render('
                    <div class="flex items-center gap-2 ml-2">
                        @include("filament.components.ai-search")
                        @include("filament.components.impersonate-select")
                    </div>
                ');
            })
            ->login(Login::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->brandName($branding['club_name'])
            ->brandLogo(function() use ($branding) {
                $logoUrl = web_asset($branding['team_logo']['paths']['velke'] ?? '/assets/img/loga/logo_kbelsti_sokoli_velke.png', false);

                return new HtmlString('
                    <div class="flex items-center gap-2 sm:gap-3 group">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 bg-white rounded-xl sm:rounded-2xl flex items-center justify-center p-1.5 sm:p-2 transition-all group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-primary/5 border border-slate-100">
                            <img src="'.$logoUrl.'" class="max-w-full max-h-full object-contain" alt="">
                        </div>
                        <div class="flex flex-col leading-tight">
                            <span class="text-[12px] sm:text-sm font-black uppercase tracking-tight text-secondary group-hover:text-primary transition-colors">'.$branding['club_short_name'].'</span>
                            <span class="text-[9px] sm:text-[10px] uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400 font-bold group-hover:text-slate-600 transition-colors">Administrace</span>
                        </div>
                    </div>
                ');
            })
            ->favicon(web_asset($branding['team_logo']['paths']['mini'] ?? '/favicon.ico', false))
            ->font('Instrument Sans')
            ->userMenuItems([
                'member_section' => MenuItem::make()
                    ->label(fn () => __('admin.navigation.pages.member_section'))
                    ->url(fn () => route('member.dashboard'))
                    ->icon(new HtmlString('<i class="fa-light fa-users-viewfinder fa-fw"></i>')),
                'public_web' => MenuItem::make()
                    ->label(fn () => __('admin.navigation.pages.public_web'))
                    ->url(fn () => route('public.home'))
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
                \App\Http\Middleware\PerformanceProfilingMiddleware::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\PerformanceProfilingMiddleware::class,
                Authenticate::class,
                '2fa.required',
                '2fa.timeout',
            ]);
    }
}
