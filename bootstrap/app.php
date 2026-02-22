<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schema;
use App\Models\CronTask;
use App\Jobs\RunCronTaskJob;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/public.php'));

            Route::middleware('web')
                ->group(base_path('routes/member.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withSchedule(function (Schedule $schedule) {
        // Dynamická registrace úloh z databáze
        if (!app()->runningInConsole() || Schema::hasTable('cron_tasks')) {
            try {
                CronTask::where('is_active', true)->each(function ($task) use ($schedule) {
                    $schedule->job(new RunCronTaskJob($task))
                        ->cron($task->expression)
                        ->name($task->name)
                        ->withoutOverlapping();
                });
            } catch (\Exception $e) {
                // Tichý fail, pokud DB není připravena (např. při první migraci)
            }
        }
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Vlastní middleware skupiny pro přehlednou správu přístupů
        // Pozn.: Skupina 'web' je již aplikována v bootstrappingu rout výše.
        // alias pro kontrolu aktivního účtu
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'public.maintenance' => \App\Http\Middleware\PublicMaintenanceMiddleware::class,
            '2fa.required' => \App\Http\Middleware\EnsureTwoFactorEnabled::class,
            '2fa.timeout' => \App\Http\Middleware\CheckTwoFactorTimeout::class,
            'minify.html' => \App\Http\Middleware\MinifyHtmlMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [
            // \App\Http\Middleware\MinifyHtmlMiddleware::class, // Dočasně vypnuto pro stabilitu Livewire/Filamentu
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

        $middleware->web(append: [
            \App\Http\Middleware\RedirectMiddleware::class,
            \App\Http\Middleware\SetLocaleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
