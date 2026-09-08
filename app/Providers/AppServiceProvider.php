<?php

namespace App\Providers;

use App\Livewire\Public\HeroEvents;
use App\Livewire\Public\StandingsTable;
use App\Livewire\Public\TeamSeasonStats;
use App\Models\Announcement;
use App\Models\BasketballMatch;
use App\Models\ClubCompetition;
use App\Models\ClubEvent;
use App\Models\Gallery;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\HelpFaq;
use App\Models\HelpQuickAction;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Opponent;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\PhotoPool;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Setting;
use App\Models\StatisticRow;
use App\Models\Team;
use App\Models\Training;
use App\Models\UserSeasonConfig;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Observers\MatchPredictionObserver;
use App\Observers\PerformanceObserver;
use App\Observers\UserSeasonConfigObserver;
use App\Services\AuditLogService;
use App\Services\BrandingService;
use App\Services\Communication\CommunicationService;
use App\Services\Member\MemberContext;
use App\Services\PerformanceService;
use App\Services\SeoService;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Contracts\StatNormalizerInterface;
use App\Services\Stats\Fetchers\CzBasketballFetcher;
use App\Services\Stats\Legacy\LegacyFileClassifier;
use App\Services\Stats\Legacy\LegacyImportService;
use App\Services\Stats\Normalizers\OpenAiNormalizer;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\OpponentSyncService;
use App\Services\Stats\Sync\RosterSyncService;
use App\Services\Stats\Sync\StatisticSetService;
use App\Services\Stats\Sync\StatisticSyncService;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\View\PanelsRenderHook;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();

            return app()->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });
        $this->app->bind(
            ResetPassword::class,
            ResetPasswordNotification::class
        );

        $this->app->bind(
            VerifyEmail::class,
            VerifyEmailNotification::class
        );

        $this->app->singleton(AuditLogService::class, function ($app) {
            return new AuditLogService;
        });

        $this->app->singleton(BrandingService::class, function ($app) {
            return new BrandingService;
        });

        $this->app->singleton(PerformanceService::class);

        $this->app->singleton(CommunicationService::class);

        $this->app->singleton(MemberContext::class);

        $this->app->bind(
            StatFetcherInterface::class,
            CzBasketballFetcher::class
        );

        $this->app->bind(
            StatNormalizerInterface::class,
            OpenAiNormalizer::class
        );

        $this->app->singleton(RosterSyncService::class);
        $this->app->singleton(StatisticSetService::class);
        $this->app->singleton(OpponentSyncService::class);
        $this->app->singleton(MatchSyncService::class);
        $this->app->singleton(StatisticSyncService::class);
        $this->app->singleton(LegacyFileClassifier::class);
        $this->app->singleton(LegacyImportService::class);

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
        // 1. Explicitní registrace Livewire komponent pro zajištění správné discovery v produkci (zejména pro lazy loading)
        // Musí proběhnout co nejdříve, před případnými chybami v bootování ostatních služeb
        if (class_exists(Livewire::class)) {
            // Registrujeme obě varianty názvů (pomlčkovou pro náš kód, tečkovou pro standardní Livewire discovery)
            Livewire::component('public-hero-events', HeroEvents::class);
            Livewire::component('public.hero-events', HeroEvents::class);

            Livewire::component('public-standings-table', StandingsTable::class);
            Livewire::component('public.standings-table', StandingsTable::class);

            Livewire::component('public-team-season-stats', TeamSeasonStats::class);
            Livewire::component('public.team-season-stats', TeamSeasonStats::class);
        }

        // Vynucení kořenové URL podle konfigurace pro správné generování odkazů v mailech a CLI
        if ($appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
        }

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

            try {
                return is_callable($previousHandler) ? $previousHandler($errno, $errstr, $errfile, $errline) : false;
            } catch (\Throwable $e) {
                // V testech může previous handler (např. Symfony) selhat na chybějícím requestu/session
                if (app()->runningUnitTests()) {
                    return true;
                }
                throw $e;
            }
        });

        UserSeasonConfig::observe(UserSeasonConfigObserver::class);

        // Načtení a aplikace výkonnostních nastavení z DB (pouze pokud neběžíme v konzoli nebo neběžíme optimize)
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            app(PerformanceService::class)->bootSettings();
        }

        // Deaktivace Telescope na produkci, pokud není explicitně vynucen přes TELESCOPE_ENABLED=true
        // a současně omezujeme zápis do DB při vysoké zátěži
        if (app()->isProduction() && config('telescope.enabled') && ! config('app.telescope_enabled')) {
            config(['telescope.enabled' => false]);
        }

        // Vlastní Blade direktiva pro fragment caching
        Blade::directive('cacheFragment', function ($expression) {
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

        Blade::directive('endCacheFragment', function () {
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

        Blade::directive('wireNavigate', function () {
            return "<?php echo config('performance.features.livewire_navigate', false) ? 'wire:navigate' : ''; ?>";
        });

        Schema::defaultStringLength(191);

        // LanguageSwitch configuration updated for v5
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['cs', 'en'])
                ->visible(
                    outsidePanels: true,
                    insidePanels: false,
                )
                ->outsidePanelsRenderHook(PanelsRenderHook::BODY_START);
        });

        // Optimalizovaná registrace observerů - pouze pro zápisové požadavky
        if (! $this->app->runningInConsole()
            && ! request()->isMethod('GET')
            && ! request()->is('assets/*', 'livewire/livewire.js')) {
            $this->registerObservers();
        }

        $this->registerViewComposers();
    }

    /**
     * Registrace observerů pro invalidaci cache.
     */
    protected function registerObservers(): void
    {
        $models = [
            Post::class,
            BasketballMatch::class,
            Team::class,
            Training::class,
            Setting::class,
            Page::class,
            PageBlock::class,
            Menu::class,
            MenuItem::class,
            Announcement::class,
            MediaAsset::class,
            Gallery::class,
            PhotoPool::class,
            HelpCategory::class,
            HelpArticle::class,
            HelpFaq::class,
            HelpQuickAction::class,
            Partner::class,
            Opponent::class,
            PostCategory::class,
            ClubCompetition::class,
            ClubEvent::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $model::observe(PerformanceObserver::class);
            }
        }

        BasketballMatch::observe(MatchPredictionObserver::class);
        StatisticRow::observe(MatchPredictionObserver::class);
    }

    /**
     * Registrace view composerů s optimalizovaným rozsahem.
     */
    protected function registerViewComposers(): void
    {
        // Omezujeme pouze na hlavní layouty a stránky, nikoliv na každý malý komponent (výkon)
        $targets = [
            'layouts.*',
            'public.*',
            'member.*',
            'auth.*',
            'errors.*',
            'filament-panels::layout',
            'filament-panels::pages.*',
        ];

        View::composer($targets, function ($view) {
            // Statická cache pro minimalizaci DB dotazů v rámci jednoho requestu
            static $cachedData = [];
            static $unreadCount = [];

            $viewName = $view->getName();

            // Přeskočíme interní livewire komponenty a drobné prvky, pokud už mají data z layoutu
            // (Laravel sice static cache má, ale i tak je tam režie volání closure)
            if (str_contains($viewName, 'livewire.') || str_contains($viewName, 'components.')) {
                return;
            }

            $brandingService = app(BrandingService::class);
            $communicationService = app(CommunicationService::class);

            $audience = (str_starts_with($viewName, 'member.') || str_contains($viewName, 'filament-panels::')) ? 'member' : 'public';
            try {
                $locale = app()->getLocale();
            } catch (\Throwable $e) {
                $locale = config('app.locale', 'cs');
            }
            $userId = auth()->id() ?: 0;

            if (! isset($cachedData[$locale])) {
                try {
                    $cacheKey = 'view_composer_data_'.$locale;
                    $cachedData[$locale] = Cache::remember($cacheKey, 3600, function () use ($brandingService, $communicationService) {
                        $branding = $brandingService->getSettings();
                        $branding['club_name'] = $brandingService->replacePlaceholders($branding['club_name']);
                        $branding['club_short_name'] = $brandingService->replacePlaceholders($branding['club_short_name']);
                        $branding['slogan'] = $brandingService->replacePlaceholders($branding['slogan'] ?? '');

                        return [
                            'branding' => $branding,
                            'branding_css' => $brandingService->getCssVariables(),
                            'announcements_public' => $communicationService->getActiveAnnouncements('public'),
                            'announcements_member' => $communicationService->getActiveAnnouncements('member'),
                        ];
                    });
                } catch (\Throwable $e) {
                    $cachedData[$locale] = [
                        'branding' => [],
                        'branding_css' => '',
                        'announcements_public' => collect(),
                        'announcements_member' => collect(),
                    ];
                }
            }

            $currentData = $cachedData[$locale];

            $view->with([
                'branding' => $currentData['branding'],
                'branding_css' => $currentData['branding_css'],
                'announcements' => $currentData["announcements_{$audience}"],
            ]);

            // SEO pouze pro veřejné layouty
            if ($audience === 'public' && ! isset($view->seo)) {
                $seoService = app(SeoService::class);
                $model = $view->page ?? $view->post ?? $view->news ?? $view->team ?? $view->gallery ?? $view->pool ?? null;
                $view->with('seo', $seoService->getMetadata($model));
            }

            if ($userId && ! isset($unreadCount[$userId])) {
                $unreadCount[$userId] = auth()->user()->unreadNotifications()->count();
                $view->with('unreadNotificationsCount', $unreadCount[$userId]);
            }
        });
    }
}
