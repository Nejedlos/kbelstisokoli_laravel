<?php

use App\Jobs\RunCronTaskJob;
use App\Models\CronTask;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$app = Application::configure(basePath: dirname(__DIR__))
    ->booting(function ($app) {
        // 1. Nastavení cest (Environment a Public) co nejdříve
        // Optimalizováno pro localhost a produkci (Webglobe)

        // Nastavení cesty k .env (v public/ složce na produkci pro index.php)
        if (! $app->runningInConsole()) {
            $envPath = file_exists(base_path('.env')) ? base_path() : base_path('public');
            $app->useEnvironmentPath($envPath);
        }

        // Nastavení public_path - musí fungovat i v Console (pro importy, seedy, media library)
        // Zabezpečení pro localhost: pokud jsme v local prostředí a produkční cesta neexistuje, nulujeme ji
        $publicPath = env('PROD_PUBLIC_PATH');

        if (env('PUBLIC_PATH_MODE') !== 'external' || ! $publicPath) {
            $publicPath = null;
        } elseif (! file_exists($publicPath) && env('APP_ENV') === 'local') {
            $publicPath = null;
        }

        if (! $publicPath && ! $app->runningInConsole() && isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === 'index.php') {
            $publicPath = dirname($_SERVER['SCRIPT_FILENAME']);
        }

        if (! $publicPath) {
            $publicPath = env('APP_PUBLIC_PATH');
            // Zabezpečení pro localhost: pokud cesta neexistuje a jsme v local, nepoužívat ji
            if ($publicPath && ! file_exists($publicPath) && env('APP_ENV') === 'local') {
                $publicPath = null;
            }
        }

        if ($publicPath) {
            $app->usePublicPath($publicPath);
            $app->instance('path.public', $publicPath);
            config(['filesystems.disks.public_path.root' => $publicPath]);
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
        if (! app()->runningInConsole()) {
            return;
        }

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
        } catch (\Throwable $e) {
            // Tichý fail, pokud DB není připravena
        }

        /*
        |--------------------------------------------------------------------------
        | Scheduler Heartbeat pro Debug Panel
        |--------------------------------------------------------------------------
        */
        $schedule->call(function () {
            \Illuminate\Support\Facades\Cache::put('scheduler_heartbeat', now());
        })->everyMinute();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Globální middleware pro detekci screenshot režimu (musí být co nejdříve)
        $middleware->prepend(\App\Http\Middleware\DetectScreenshotMode::class);

        // Vlastní middleware skupiny pro přehlednou správu přístupů
        // Pozn.: Skupina 'web' je již aplikována v bootstrappingu rout výše.
        // alias pro kontrolu aktivního účtu
        $middleware->alias([
            'panel' => \Filament\Http\Middleware\SetUpPanel::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'public.maintenance' => \App\Http\Middleware\PublicMaintenanceMiddleware::class,
            '2fa.required' => \App\Http\Middleware\EnsureTwoFactorEnabled::class,
            '2fa.timeout' => \App\Http\Middleware\CheckTwoFactorTimeout::class,
            'minify.html' => \App\Http\Middleware\MinifyHtmlMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'redirects' => \App\Http\Middleware\RedirectMiddleware::class,
            'not_found_logger' => \App\Http\Middleware\NotFoundLoggerMiddleware::class,
        ]);

        $middleware->web(prepend: [
            \App\Http\Middleware\PerformanceProfilingMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocaleMiddleware::class,
            \App\Http\Middleware\FullPageCacheMiddleware::class,
            \App\Http\Middleware\AddRequestIdToResponse::class,
            \App\Http\Middleware\MinifyHtmlMiddleware::class,
            \App\Http\Middleware\InjectFeedbackWidget::class,
            \App\Http\Middleware\NotFoundLoggerMiddleware::class,
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
            'feedback',
            'feedback/*',
            '*/feedback',
            '*/feedback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('admin*') || $request->is('clenska-sekce*')) {
                // Před zobrazením 401 chybové stránky uložíme současnou URL,
                // aby se sem uživatel mohl po přihlášení vrátit.
                \App\Support\AuthRedirect::storeIntendedUrl($request->fullUrl());

                return response()->view('errors.shot-clock', [], 401);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419 && $request->is('logout')) {
                return redirect()->to('/');
            }
        });

        // Odeslání e-mailu s chybou na produkci (vynechá 4xx chyby)
        $exceptions->report(function (\Throwable $e) {
            try {
                $reportEnvs = config('mail.error_reporting.environments', ['production']);
                if (! in_array(app()->environment(), $reportEnvs)) {
                    return;
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return; // nehlásíme 4xx
                }

                $request = request();

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
                    if (\Illuminate\Support\Facades\Auth::check()) {
                        $u = \Illuminate\Support\Facades\Auth::user();
                        $user = [
                            'id' => $u->id ?? null,
                            'email' => $u->email ?? null,
                            'name' => $u->name ?? null,
                        ];
                    }
                } catch (\Throwable $ignored) {
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
                    if (\App\Support\ErrorMailThrottle::shouldThrottle($e, $request ? $request->fullUrl() : null)) {
                        return;
                    }

                    \Illuminate\Support\Facades\Mail::to($to)
                        ->send((new \App\Mail\ErrorMail($report))->from($from, config('mail.from.name')));
                }
            } catch (\Throwable $ignored) {
                // Zaloggujeme selhání odeslání chybového e-mailu pro následnou diagnostiku
                try {
                    \Illuminate\Support\Facades\Log::error('Error report email failed', [
                        'message' => $ignored->getMessage(),
                        'exception' => get_class($ignored),
                    ]);
                } catch (\Throwable $e2) {
                }
            }
        });

        // Vlastní 500 stránka s kopírovatelnými debug informacemi (bez citlivých dat)
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! app()->environment('production')) {
                return null; // nezasahujeme mimo produkci
            }

            // Pokud je to chyba 419 (CSRF token mismatch), vracíme null, aby proběhlo standardní ošetření
            // (pokud není request na logout, což už je ošetřeno výše)
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 419) {
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
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
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
