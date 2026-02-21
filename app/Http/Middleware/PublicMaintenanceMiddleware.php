<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\BrandingService;
use Symfony\Component\HttpFoundation\Response;

class PublicMaintenanceMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $branding = app(BrandingService::class)->getSettings();

        // Bypass pro adminy a oprávněné osoby (vidí web i během údržby)
        if (auth()->check() && auth()->user()->can('access_admin')) {
            return $next($request);
        }

        // Pokud je zapnutý maintenance mode, přesměrujeme všechny veřejné podstránky na úvodní stránku
        if (data_get($branding, 'maintenance_mode') &&
            $request->routeIs('public.*') &&
            !$request->routeIs('public.home')) {
            return redirect()->route('public.home');
        }

        return $next($request);
    }
}
