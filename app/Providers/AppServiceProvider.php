<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        $this->app->singleton(\App\Services\BrandingService::class, function ($app) {
            return new \App\Services\BrandingService;
        });

        $this->app->singleton(\App\Services\PerformanceService::class);

        $this->app->singleton(\App\Services\Communication\CommunicationService::class);

        $this->app->singleton(\App\Services\Member\MemberContext::class);

        $this->app->bind(
            \App\Services\Stats\Contracts\StatFetcherInterface::class,
            \App\Services\Stats\Fetchers\CzBasketballFetcher::class
        );

        $this->app->bind(
            \App\Services\Stats\Contracts\StatNormalizerInterface::class,
            \App\Services\Stats\Normalizers\OpenAiNormalizer::class
        );

        $this->app->singleton(\App\Services\Stats\Sync\RosterSyncService::class);
        $this->app->singleton(\App\Services\Stats\Sync\StatisticSetService::class);
        $this->app->singleton(\App\Services\Stats\Sync\OpponentSyncService::class);
        $this->app->singleton(\App\Services\Stats\Sync\MatchSyncService::class);
        $this->app->singleton(\App\Services\Stats\Sync\StatisticSyncService::class);
        $this->app->singleton(\App\Services\Stats\Legacy\LegacyFileClassifier::class);
        $this->app->singleton(\App\Services\Stats\Legacy\LegacyImportService::class);

        // Robustní fix pro Vite manifest na Webglobe hostingu (subdomény vs. root)
        $this->app->singleton(Vite::class, function ($app) {
            return new class extends Vite
            {
                protected function manifestPath($buildDirectory): string
                {
                    $path = parent::manifestPath($buildDirectory);

                    if (file_exists($path)) {
                        return $path;
                    }

                    // Fallback: Pokud manifest není v public_path (subdoména),
                    // zkusíme ho najít v base_path('public/build/manifest.json') - root aplikace.
                    $fallback = base_path('public/'.$buildDirectory.'/manifest.json');
                    if (file_exists($fallback)) {
                        return $fallback;
                    }

                    // Fallback 2: Zkusíme cestu z .env (PROD_PUBLIC_PATH)
                    if ($prodPath = env('PROD_PUBLIC_PATH')) {
                        $fallbackProd = rtrim($prodPath, '/').'/'.$buildDirectory.'/manifest.json';
                        if (file_exists($fallbackProd)) {
                            return $fallbackProd;
                        }
                    }

                    return $path;
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Zvýšení paměťového limitu pro administrativu (zpracování velkých obrázků)
        if (request()->is('admin*')) {
            @ini_set('memory_limit', '512M');
        }

        // Fix pro Webglobe: potlačení notice o tempnam fallbacku, která shazuje aplikaci na produkci
        // Tento handler registrujeme v boot() metodě, aby byl co nejvíce robustní a přežil Laravel bootstrap
        $previousHandler = set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$previousHandler) {
            // Fix pro Webglobe: potlačení notice o tempnam fallbacku, která shazuje aplikaci na produkci
            if ($errno === E_NOTICE && (str_contains($errstr, 'tempnam()') && str_contains($errstr, 'temporary directory'))) {
                return true; // Ignorovat tuto konkrétní notice
            }

            // Fix pro Livewire 3: potlačení chyby "Undefined array key children" při mazání/změně cache
            // Tato chyba je neškodná a vyřeší se refreshem stránky, ale nesmí shodit celou aplikaci (Error Report)
            if (str_contains($errstr, 'Undefined array key "children"') && str_contains($errfile, 'SupportNestingComponents.php')) {
                return true;
            }

            // Fix pro Livewire 3: potlačení chyby "Undefined array key locale" v SupportLocales.php
            // K této chybě dochází v SupportLocales.php:11, když v memo chybí klíč locale.
            // Typicky se to děje v SystemConsole při priming cache, kdy se mění locale v rámci requestu.
            if (str_contains($errstr, 'Undefined array key "locale"') && str_contains($errfile, 'SupportLocales.php')) {
                return true;
            }

            return is_callable($previousHandler) ? $previousHandler($errno, $errstr, $errfile, $errline) : false;
        });

        \App\Models\UserSeasonConfig::observe(\App\Observers\UserSeasonConfigObserver::class);

        // Načtení a aplikace výkonnostních nastavení z DB (pouze pokud neběžíme v konzoli nebo neběžíme optimize)
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            app(\App\Services\PerformanceService::class)->bootSettings();
        }

        // Vlastní Blade direktiva pro fragment caching
        \Illuminate\Support\Facades\Blade::directive('cacheFragment', function ($expression) {
            return "<?php
                \$__cache_args = [{$expression}];
                \$__cache_key = \$__cache_args[0] ?? 'fragment_'.md5(request()->fullUrl());
                \$__cache_ttl = \$__cache_args[1] ?? config('performance.cache_ttl.fragments', 3600);
                \$__should_cache = config('performance.features.fragment_cache', false);

                if (\$__should_cache && Cache::has(\$__cache_key)) {
                    echo Cache::get(\$__cache_key);
                    \$__skip_render = true;
                } else {
                    \$__skip_render = false;
                    ob_start();
                }

                if (!\$__skip_render):
            ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endCacheFragment', function () {
            return '<?php
                endif;
                if (!$__skip_render) {
                    $__cache_content = ob_get_clean();
                    if ($__should_cache) {
                        Cache::put($__cache_key, $__cache_content, $__cache_ttl);
                    }
                    echo $__cache_content;
                }
            ?>';
        });

        \Illuminate\Support\Facades\Blade::directive('wireNavigate', function () {
            return "<?php echo config('performance.features.livewire_navigate', false) ? 'wire:navigate' : ''; ?>";
        });

        Schema::defaultStringLength(191);


        // LanguageSwitch configuration updated for v5
        \BezhanSalleh\LanguageSwitch\LanguageSwitch::configureUsing(function (\BezhanSalleh\LanguageSwitch\LanguageSwitch $switch) {
            $switch
                ->locales(['cs', 'en'])
                ->visible(
                    outsidePanels: true,
                    insidePanels: false,
                )
                ->outsidePanelsRenderHook(\Filament\View\PanelsRenderHook::BODY_START);
        });

        // Registrace Performance Observeru pro automatické mazání cache (pouze pokud neběžíme v konzoli a není to static asset)
        // Optimalizováno: Registrujeme pouze pro požadavky, které mohou měnit data (POST, PUT, DELETE, PATCH)
        if (! $this->app->runningInConsole()
            && ! request()->isMethod('GET')
            && ! request()->is('assets/*', 'livewire/livewire.js')) {
            $models = [
                \App\Models\Post::class,
                \App\Models\BasketballMatch::class,
                \App\Models\Team::class,
                \App\Models\Training::class,
                \App\Models\Setting::class,
                \App\Models\Page::class,
                \App\Models\PageBlock::class,
                \App\Models\Menu::class,
                \App\Models\MenuItem::class,
                \App\Models\Announcement::class,
                \App\Models\MediaAsset::class,
                \App\Models\Gallery::class,
                \App\Models\PhotoPool::class,
                \App\Models\HelpCategory::class,
                \App\Models\HelpArticle::class,
                \App\Models\HelpFaq::class,
                \App\Models\HelpQuickAction::class,
                \App\Models\Partner::class,
                \App\Models\Opponent::class,
                \App\Models\Page::class,
                \App\Models\Post::class,
                \App\Models\PostCategory::class,
                \App\Models\ClubCompetition::class,
            ];

            foreach ($models as $model) {
                if (class_exists($model)) {
                    $model::observe(\App\Observers\PerformanceObserver::class);
                }
            }

            \App\Models\BasketballMatch::observe(\App\Observers\MatchPredictionObserver::class);
            \App\Models\StatisticRow::observe(\App\Observers\MatchPredictionObserver::class);
        }

        \Illuminate\Support\Facades\View::composer(['layouts.*', 'public.*', 'member.*', 'auth.*', 'errors.*', 'filament-panels::layout.*', 'filament-panels::pages.*'], function ($view) {
            // Statická cache pro minimalizaci DB dotazů v rámci jednoho requestu
            static $cachedData = null;
            static $unreadCount = null;

            $brandingService = app(\App\Services\BrandingService::class);
            $communicationService = app(\App\Services\Communication\CommunicationService::class);

            $viewName = $view->getName();
            $audience = (str_starts_with($viewName, 'member.') || str_contains($viewName, 'filament-panels::')) ? 'member' : 'public';

            if ($cachedData === null) {
                $branding = $brandingService->getSettings();
                $branding['club_name'] = $brandingService->replacePlaceholders($branding['club_name']);
                $branding['club_short_name'] = $brandingService->replacePlaceholders($branding['club_short_name']);
                $branding['slogan'] = $brandingService->replacePlaceholders($branding['slogan'] ?? '');

                try {
                    $cacheKey = 'view_composer_data_' . app()->getLocale();
                    $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($brandingService, $communicationService, $branding) {
                        return [
                            'branding' => $branding,
                            'branding_css' => $brandingService->getCssVariables(),
                            'announcements_public' => $communicationService->getActiveAnnouncements('public'),
                            'announcements_member' => $communicationService->getActiveAnnouncements('member'),
                        ];
                    });
                } catch (\Throwable $e) {
                    // Fallback při selhání cache (lock timeout)
                    $cachedData = [
                        'branding' => $branding,
                        'branding_css' => $brandingService->getCssVariables(),
                        'announcements_public' => $communicationService->getActiveAnnouncements('public'),
                        'announcements_member' => $communicationService->getActiveAnnouncements('member'),
                    ];
                }
            }

            $view->with('branding', $cachedData['branding']);
            $view->with('branding_css', $cachedData['branding_css']);
            $view->with('announcements', $cachedData["announcements_{$audience}"]);

            // Přidání SEO metadat pro public layout, pokud už nejsou nastaveny
            if ($audience === 'public' && ! isset($view->seo)) {
                $seoService = app(\App\Services\SeoService::class);
                $model = $view->page ?? $view->post ?? $view->news ?? $view->team ?? $view->gallery ?? $view->pool ?? null;
                $view->with('seo', $seoService->getMetadata($model));
            }

            if (auth()->check()) {
                if ($unreadCount === null) {
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                }
                $view->with('unreadNotificationsCount', $unreadCount);
            }
        });
    }
}
