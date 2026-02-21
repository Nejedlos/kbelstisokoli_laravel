<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.public', function ($view) {
            $brandingService = app(\App\Services\BrandingService::class);
            $view->with('branding', $brandingService->getSettings());
            $view->with('branding_css', $brandingService->getCssVariables());
        });
    }
}
