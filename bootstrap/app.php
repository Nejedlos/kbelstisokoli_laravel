<?php

use App\Http\Middleware\AddRequestIdToResponse;
use App\Http\Middleware\CheckTwoFactorTimeout;
use App\Http\Middleware\DetectScreenshotMode;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\FullPageCacheMiddleware;
use App\Http\Middleware\InjectFeedbackWidget;
use App\Http\Middleware\MinifyHtmlMiddleware;
use App\Http\Middleware\NotFoundLoggerMiddleware;
use App\Http\Middleware\PerformanceProfilingMiddleware;
use App\Http\Middleware\PublicMaintenanceMiddleware;
use App\Http\Middleware\RedirectMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Http\Middleware\NormalizeSignedUrlParameters;
use App\Jobs\RunCronTaskJob;
use App\Mail\ErrorMail;
use App\Models\CronTask;
use App\Support\AuthRedirect;
use App\Support\ErrorMailThrottle;
use Filament\Http\Middleware\SetUpPanel;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$app = Application::configure(basePath: dirname(__DIR__))
    ->booting(function ($app) {
        // 1. Nastavení cest (Environment a Public) - část logiky závislá na env()
        // Optimalizováno pro localhost a produkci (Webglobe)

        // Fix pro Laravel 13: Zajistit načtení .env co nejdříve, pokud není v cache
        if (! $app->configurationIsCached()) {
            $base = $app->basePath();
            $envPath = file_exists($base.'/.env') ? $base : $base.'/public';
            $app->useEnvironmentPath($envPath);
        }

        // Fix pro správnou PHP binárku na produkci (pro Scheduler a subprocesy)
        if (app()->runningInConsole() && $app->environment('production') && $php = (config('app.prod_php_binary') ?: env('PROD_PHP_BINARY'))) {
            putenv("PHP_BINARY=$php");
        }

        // Nastavení public_path - musí fungovat i v Console (pro importy, seedy, media library)
        // Zabezpečení pro localhost: pokud jsme v local prostředí a produkční cesta neexistuje, nulujeme ji
        $publicPath = config('app.prod_public_path') ?: env('PROD_PUBLIC_PATH');

        if ((config('app.public_path_mode') ?: env('PUBLIC_PATH_MODE')) !== 'external' || ! $publicPath) {
            $publicPath = null;
        } elseif (! file_exists($publicPath) && $app->environment('local')) {
            $publicPath = null;
        }

        if (! $publicPath && ! $app->runningInConsole() && isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'index.php') {
            $publicPath = dirname($_SERVER['SCRIPT_FILENAME']);
        }

        if (! $publicPath) {
            $publicPath = config('app.public_path') ?: env('APP_PUBLIC_PATH');
            // Zabezpečení pro localhost: pokud cesta neexistuje a jsme v local, nepoužívat ji
            if ($publicPath && ! file_exists($publicPath) && $app->environment('local')) {
                $publicPath = null;
            }
        }

        if ($publicPath) {
            $app->usePublicPath($publicPath);
            $app->instance('path.public', $publicPath);
            config(['filesystems.disks.public_path.root' => $publicPath]);
        }

        // Vynucení HTTPS na produkci pro stabilní generování assetů (Vite, asset(), ...)
        // Důležité po optimize:clear, kdy se spoléháme na dynamickou detekci URL
        if ($app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/member.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/public.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule) {
        // Zabezpečení pro případy, kdy DB není dostupná (např. při setupu nebo migracích)
        $argv = $_SERVER['argv'] ?? [];
        if (count(array_intersect(['help', 'list', 'migrate', 'key:generate', 'package:discover', 'optimize', 'filament:upgrade'], $argv)) > 0) {
            return;
        }

        // Dynamická registrace úloh z databáze
        try {
            if (Schema::hasTable('cron_tasks')) {
                CronTask::where('is_active', true)->each(function ($task) use ($schedule) {
                    $schedule->job(new RunCronTaskJob($task))
                        ->cron($task->expression)
                        ->name($task->name)
                        ->withoutOverlapping();
                });
            }
        } catch (Throwable $e) {
            // Tichý fail, pokud DB není připravena
        }

        /*
        |--------------------------------------------------------------------------
        | Scheduler Heartbeat pro Debug Panel
        |--------------------------------------------------------------------------
        */
        $schedule->call(function () {
            Cache::put('scheduler_heartbeat', now());
            Log::info('Scheduler Heartbeat tick.');
        })->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Globální middleware pro detekci screenshot režimu (musí být co nejdříve)
        // Přesunuto do web group append pro správnou session a auth
        // $middleware->prepend(\App\Http\Middleware\DetectScreenshotMode::class);

        // Vlastní middleware skupiny pro přehlednou správu přístupů
        // Pozn.: Skupina 'web' je již aplikována v bootstrappingu rout výše.
        // alias pro kontrolu aktivního účtu
        $middleware->alias([
            'panel' => SetUpPanel::class,
            'active' => EnsureUserIsActive::class,
            'public.maintenance' => PublicMaintenanceMiddleware::class,
            '2fa.required' => EnsureTwoFactorEnabled::class,
            '2fa.timeout' => CheckTwoFactorTimeout::class,
            'minify.html' => MinifyHtmlMiddleware::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'redirects' => RedirectMiddleware::class,
            'not_found_logger' => NotFoundLoggerMiddleware::class,
        ]);

        $middleware->web(append: [
            PerformanceProfilingMiddleware::class,
            SecurityHeadersMiddleware::class,
            DetectScreenshotMode::class,
            SetLocaleMiddleware::class,
            FullPageCacheMiddleware::class,
            AddRequestIdToResponse::class,
            MinifyHtmlMiddleware::class,
            InjectFeedbackWidget::class,
            // Strip neznámé query parametry z podepsaných URL (např. UTM z e-mailů), aby nepadala validace podpisu
            NormalizeSignedUrlParameters::class,
            NotFoundLoggerMiddleware::class,
            \App\Http\Middleware\Restrict2FADeactivation::class,
            \App\Http\Middleware\InternalAnalyticsMiddleware::class,
            'active',
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->priority([
            StartSession::class,
            DetectScreenshotMode::class,
            Authenticate::class,
            EnsureUserIsActive::class,
            Filament\Http\Middleware\Authenticate::class,
            Authorize::class,
            EnsureTwoFactorEnabled::class,
            CheckTwoFactorTimeout::class,
        ]);

        $middleware->group('member', [
            'auth',
            'verified',
            'active',
            'permission:view_member_section',
        ]);

        $middleware->group('admin', [
            'auth',
            'active',
            '2fa.required',
            '2fa.timeout',
            'permission:access_admin',
        ]);

        $middleware->validateCsrfTokens(except: [
            // Feedback widget v aplikaci používá standardní CSRF
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('admin*') || $request->is('clenska-sekce*')) {
                // Před zobrazením 401 chybové stránky uložíme současnou URL,
                // aby se sem uživatel mohl po přihlášení vrátit.
                AuthRedirect::storeIntendedUrl($request->fullUrl());

                return response()->view('errors.shot-clock', [], 401);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && $request->is('logout')) {
                return redirect()->to('/');
            }
        });

        // Odeslání e-mailu s chybou na produkci (vynechá 4xx chyby)
        $exceptions->report(function (Throwable $e) {
            if (app()->bound('log')) {
                Log::error('App Exception: '.$e->getMessage(), [
                    'exception' => $e,
                    'url' => app()->bound('request') ? request()->fullUrl() : 'CLI',
                ]);
            } else {
                error_log('App Exception (Log facade not bound): '.$e->getMessage());
            }

            try {
                $reportEnvs = config('mail.error_reporting.environments', ['production']);
                if (! in_array(app()->environment(), $reportEnvs)) {
                    return;
                }

                if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return; // nehlásíme 4xx
                }

                $request = app()->bound('request') ? request() : null;

                // Sestavení hlášení s očištěním citlivých údajů
                $sanitize = function (array $data) use (&$sanitize): array {
                    $sensitive = ['password', 'password_confirmation', '_token', 'current_password', 'token'];
                    foreach ($data as $k => $v) {
                        if (in_array(strtolower((string) $k), $sensitive, true)) {
                            $data[$k] = '[hidden]';
                        } elseif (is_array($v)) {
                            $data[$k] = $sanitize($v);
                        }
                    }

                    return $data;
                };

                $headers = [];
                if ($request) {
                    foreach ($request->headers->all() as $k => $v) {
                        $headers[$k] = is_array($v) ? implode(', ', $v) : (string) $v;
                    }
                }

                $user = null;
                try {
                    if (Auth::check()) {
                        $u = Auth::user();
                        $user = [
                            'id' => $u->id ?? null,
                            'email' => $u->email ?? null,
                            'name' => $u->name ?? null,
                        ];
                    }
                } catch (Throwable $ignored) {
                }

                $report = [
                    'timestamp' => now()->toIso8601String(),
                    'app' => [
                        'name' => config('app.name'),
                        'env' => config('app.env'),
                        'url' => config('app.url'),
                    ],
                    'exception' => [
                        'class' => get_class($e),
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => substr($e->getTraceAsString(), 0, 20000),
                    ],
                    'request' => $request ? [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'ip' => $request->ip(),
                        'query' => $sanitize($request->query()),
                        'input' => $sanitize($request->except(['password', 'password_confirmation', '_token', 'current_password', 'token'])),
                    ] : null,
                    'headers' => $headers,
                    'server' => [
                        'php' => PHP_VERSION,
                        'sapi' => PHP_SAPI,
                        'memory_usage' => memory_get_usage(true),
                    ],
                    'user' => $user,
                ];

                $to = config('mail.error_reporting.email');
                $from = config('mail.error_reporting.sender', config('mail.from.address'));

                if ($to) {
                    // Kontrola throttling / deduplikace chybových e-mailů
                    if (ErrorMailThrottle::shouldThrottle($e, $request ? $request->fullUrl() : null)) {
                        return;
                    }

                    Mail::to($to)
                        ->send((new ErrorMail($report))->from($from, config('mail.from.name')));
                }
            } catch (Throwable $ignored) {
                // Zaloggujeme selhání odeslání chybového e-mailu pro následnou diagnostiku
                try {
                    Log::error('Error report email failed', [
                        'message' => $ignored->getMessage(),
                        'exception' => get_class($ignored),
                    ]);
                } catch (Throwable $e2) {
                }
            }
        });

        // Vlastní 500 stránka s kopírovatelnými debug informacemi (bez citlivých dat)
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! app()->environment('production')) {
                return null; // nezasahujeme mimo produkci
            }

            // Pokud je to chyba 419 (CSRF token mismatch), vracíme null, aby proběhlo standardní ošetření
            // (pokud není request na logout, což už je ošetřeno výše)
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 419) {
                // Pokud je uživatel přihlášen, zkusíme mu zachovat session tím, že ho jen přesměrujeme zpět s hláškou
                if ($request->user()) {
                    return redirect()->back()->withErrors(['error' => __('Platnost relace vypršela, zkuste akci zopakovat.')]);
                }

                // Pokud je to GET požadavek na admin/členskou sekci, zobrazíme Shot clock violation
                if ($request->isMethod('GET') && ($request->is('admin*') || $request->is('clenska-sekce*'))) {
                    return response()->view('errors.shot-clock', [], 419);
                }

                return null;
            }

            // Pouze pro neošetřené chyby 500+
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                return null;
            }

            // Stejné čištění dat jako pro e-mail
            $sanitize = function (array $data) use (&$sanitize): array {
                $sensitive = ['password', 'password_confirmation', '_token', 'current_password', 'token'];
                foreach ($data as $k => $v) {
                    if (in_array(strtolower((string) $k), $sensitive, true)) {
                        $data[$k] = '[hidden]';
                    } elseif (is_array($v)) {
                        $data[$k] = $sanitize($v);
                    }
                }

                return $data;
            };

            $headers = [];
            foreach ($request->headers->all() as $k => $v) {
                $headers[$k] = is_array($v) ? implode(', ', $v) : (string) $v;
            }

            $report = [
                'timestamp' => now()->toIso8601String(),
                'app' => [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'url' => config('app.url'),
                ],
                'exception' => [
                    'class' => get_class($e),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => substr($e->getTraceAsString(), 0, 20000),
                ],
                'request' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'query' => $sanitize($request->query()),
                    'input' => $sanitize($request->except(['password', 'password_confirmation', '_token', 'current_password', 'token'])),
                ],
                'headers' => $headers,
                'server' => [
                    'php' => PHP_VERSION,
                    'sapi' => PHP_SAPI,
                    'memory_usage' => memory_get_usage(true),
                ],
            ];

            return response()->view('errors.500', ['report' => $report], 500);
        });
    })->create();

return $app;
