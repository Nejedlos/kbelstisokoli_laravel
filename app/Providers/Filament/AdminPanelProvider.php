<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EmailVerificationPrompt;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\Auth\ResetPassword;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource;
use App\Filament\Resources\Dmarc\DmarcIncidentResource;
use App\Filament\Resources\Dmarc\DmarcMailboxResource;
use App\Filament\Resources\Dmarc\DmarcReportResource;
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
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa() // Zapnutí SPA navigace (wire:navigate) pro bleskovou administraci
            ->colors(fn () => [
                'primary' => \Filament\Support\Colors\Color::hex(app(\App\Services\BrandingService::class)->getSettings()['colors']['red'] ?? '#e11d48'),
                'gray' => \Filament\Support\Colors\Color::Slate,
                'info' => \Filament\Support\Colors\Color::Sky,
                'success' => \Filament\Support\Colors\Color::Emerald,
                'warning' => \Filament\Support\Colors\Color::Amber,
                'danger' => \Filament\Support\Colors\Color::Rose,
            ])
            ->darkMode(false)
            // Vložíme vlastní CSS variables do <head> přes render hook (globálně pro barvy)
            ->renderHook('panels::head.end', function (): string {
                // Podle toho, zda jsme na auth stránkách nebo v adminu, zvolíme správný CSS entrypoint
                $isAuth = request()->routeIs('filament.admin.auth.*');
                $entrypoints = $isAuth
                    ? ['resources/css/icons-fix.css', 'resources/css/filament-auth.css']
                    : ['resources/css/icons-fix.css', 'resources/css/filament-admin.css'];

                $cropper = '';
                if (auth()->check() && ! $isAuth) {
                    $cropper = "
                        <link rel='stylesheet' href='/assets/vendor/cropper.min.css' />
                        <script src='/assets/vendor/cropper.min.js'></script>
                    ";
                }

                // Branding service vrací --color-brand-* tokens a RGB varianty
                $brandingService = app(\App\Services\BrandingService::class);
                $brandingVariables = $brandingService->getCssVariables();

                // Screenshot mode support
                $screenshotSupport = "";
                if (class_exists(\App\Support\ScreenshotMode::class)) {
                    $screenshotSupport = Blade::render('<x-screenshot.styles /><x-screenshot.scripts />');
                }

                // Pokud jsme na auth stránce, přidáme aliasy --brand-* (pro filament-auth.css)
                $authAliases = '';
                if ($isAuth) {
                    $settings = $brandingService->getSettings();
                    $colors = $settings['colors'];
                    $colorsRgb = $settings['colors_rgb'];

                    $authAliases = '
                        :root {
                            --brand-navy: '.($colors['navy'] ?? '#0b1f3a').';
                            --brand-navy-rgb: '.($colorsRgb['navy'] ?? '11, 31, 58').';
                            --brand-blue: '.($colors['blue'] ?? '#2563eb').';
                            --brand-blue-rgb: '.($colorsRgb['blue'] ?? '37, 99, 235').';
                            --brand-red: '.($colors['red'] ?? '#e11d48').';
                            --brand-red-rgb: '.($colorsRgb['red'] ?? '225, 29, 72').';
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

                // Optimalizovaný výstup bez Blade::render pro zrychlení requestu
                $viteAssets = app(\Illuminate\Foundation\Vite::class)->__invoke($entrypoints)->toHtml();
                if ($isAuth) {
                    $viteAssets .= app(\Illuminate\Foundation\Vite::class)->__invoke(['resources/js/filament-auth.js', 'resources/js/filament-error-handler.js'])->toHtml();
                }

                $favicons = '';
                try {
                    if (app()->bound('translator')) {
                        $favicons = view('partials.favicons', ['includeMain' => true])->render();
                    }
                } catch (\Throwable $e) {}

                return "
                    <meta name=\"color-scheme\" content=\"light\">
                    {$favicons}
                    {$screenshotSupport}
                    <script>
                        /**
                         * ABSOLUTNÍ VYNUCENÍ SVĚTLÉHO REŽIMU (AGRESIVNÍ)
                         * Tento skript aktivně sleduje změny na <html> elementu a okamžitě
                         * odstraňuje třídu .dark, pokud se ji jakýkoliv jiný skript (Filament, Alpine)
                         * pokusí přidat. Zároveň vynucuje světlé téma v localStorage.
                         */
                        (function() {
                            const forceLight = () => {
                                if (document.documentElement.classList.contains('dark')) {
                                    document.documentElement.classList.remove('dark');
                                }
                                if (localStorage.getItem('theme') !== 'light') {
                                    localStorage.setItem('theme', 'light');
                                }
                                // Vynucení color-scheme na elementu
                                if (document.documentElement.style.colorScheme !== 'light') {
                                    document.documentElement.style.colorScheme = 'light';
                                }
                            };

                            // 1. Okamžité spuštění
                            forceLight();

                            // 2. MutationObserver pro sledování změn (nejvíc robustní metoda)
                            const observer = new MutationObserver((mutations) => {
                                mutations.forEach((mutation) => {
                                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                        if (document.documentElement.classList.contains('dark')) {
                                            document.documentElement.classList.remove('dark');
                                        }
                                    }
                                });
                            });
                            observer.observe(document.documentElement, { attributes: true });

                            // 3. Pojistka pro eventy
                            window.addEventListener('resize', forceLight);
                            document.addEventListener('livewire:navigated', forceLight);
                            document.addEventListener('DOMContentLoaded', forceLight);

                            // 4. Globální fix pro Alpine.js store (pokud ho Filament používá)
                            document.addEventListener('alpine:init', () => {
                                if (window.Alpine && window.Alpine.store('theme')) {
                                    window.Alpine.store('theme', 'light');
                                }
                            });
                        })();

                        /**
                         * KRITICKÝ FIX: Globální potlačení Livewire 3 abort rejekcí v <head>.
                         * Tento skript zachytává \"prázdné\" rejekce, které Livewire 3 vyhazuje při přerušení
                         * asynchronního požadavku (např. při pollingu nebo navigaci).
                         *
                         * Používáme data-navigate-once, aby se listener v SPA mode nepřidával duplicitně.
                         */
                        (function() {
                            if (window.livewireAbortHandlerInitialized) return;

                            var suppress = function(e) {
                                var r = e.reason;
                                // Livewire 3 abort objekt: { status: null, body: null, json: null, errors: null }
                                // Rejekce může být i undefined/null při navigaci.
                                var isLivewireAbort = !r || (
                                    typeof r === 'object' &&
                                    'status' in r && r.status === null &&
                                    ('body' in r || 'json' in r || 'errors' in r)
                                );
                                if (isLivewireAbort) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                }
                            };
                            window.addEventListener('unhandledrejection', suppress, true);
                            window.livewireAbortHandlerInitialized = true;
                        })();
                    </script>
                    <link rel='preconnect' href='https://fonts.googleapis.com'>
                    <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
                    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Oswald:wght@200..700&display=swap' media='print' onload=\"this.media='all'\">
                    <style>
                        {$brandingVariables}
                        {$authAliases}
                        :root {
                            --font-family: 'Instrument Sans', sans-serif;
                        }
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
                    {$cropper}
                    {$viteAssets}
                ";
            })
            ->renderHook('panels::body.start', function (): string {
                if (! auth()->check() || request()->routeIs('filament.admin.auth.*')) {
                    return '';
                }

                // Obalení do jediného elementu pro stabilitu Livewire
                return Blade::render('
                    <div wire:key="body-start-container">
                        <x-loader-global title="Administrace" />
                        <x-impersonation-banner />
                        <x-impersonation-notification />
                        <livewire:sync-status-bar />
                    </div>
                ');
            })
            ->renderHook('panels::body.end', function (): string {
                if (! auth()->check() || request()->routeIs('filament.admin.auth.*')) {
                    return '';
                }

                return Blade::render('
                    <div wire:key="body-end-container">
                        <x-back-to-top />
                        <livewire:member.avatar-modal />
                    </div>
                ');
            })
            ->renderHook('panels::global-search.before', function (): string {
                if (! auth()->check()) {
                    return '';
                }

                return Blade::render('
                    <div class="flex items-center gap-1 sm:gap-2 mr-1 sm:mr-3" wire:key="topbar-left-actions">
                        @include("filament.components.standard-search")
                    </div>
                ');
            })
            ->renderHook('panels::global-search.after', function (): string {
                if (! auth()->check()) {
                    return '';
                }

                return Blade::render('
                    <div class="flex items-center gap-1 lg:gap-2 ml-1 sm:ml-2" wire:key="topbar-right-actions-container">
                        <div wire:key="language-switch-wrapper" class="flex-shrink-0">
                            @include("filament.components.language-switch")
                        </div>
                        <div class="flex items-center gap-1 lg:gap-2 flex-shrink-0" wire:key="topbar-alpine-actions">
                            @include("filament.components.ai-search")
                            @include("filament.components.impersonate-select")
                        </div>
                    </div>
                ');
            })
            ->login(Login::class)
            ->globalSearch(\App\Filament\GlobalSearch\AiGlobalSearchProvider::class)
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->brandName(fn() => app(BrandingService::class)->getSettings()['club_name'])
            ->brandLogo(function() {
                $branding = app(BrandingService::class)->getSettings();
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
            ->favicon(fn() => web_asset(app(BrandingService::class)->getSettings()['team_logo']['paths']['mini'] ?? '/favicon.ico', false))
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
            ->resources([
                DmarcAuthorizedSenderResource::class,
                DmarcIncidentResource::class,
                DmarcMailboxResource::class,
                DmarcReportResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\RealAttendance::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.sports_agenda')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.users_and_people')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.finance')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.content_and_media')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.web_settings')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.statistics_and_data') . ' > ' . __('admin.navigation.groups.external_data'))
                    ->collapsed(),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.system'))
                    ->collapsed(),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.dmarc_monitor')),
                \Filament\Navigation\NavigationGroup::make()
                    ->label(fn (): string => __('admin.navigation.groups.documentation')),
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
                \App\Http\Middleware\DetectScreenshotMode::class,
                \App\Http\Middleware\InjectFeedbackWidget::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\EnsureUserIsActive::class,
                \App\Http\Middleware\EnsureTwoFactorEnabled::class,
                \App\Http\Middleware\CheckTwoFactorTimeout::class,
            ]);
    }
}
