<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

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
    ->withMiddleware(function (Middleware $middleware): void {
        // Vlastní middleware skupiny pro přehlednou správu přístupů
        // Pozn.: Skupina 'web' je již aplikována v bootstrappingu rout výše.
        // alias pro kontrolu aktivního účtu
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'public.maintenance' => \App\Http\Middleware\PublicMaintenanceMiddleware::class,
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
            'permission:access_admin',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\RedirectMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
