<?php

namespace App\Http\Middleware;

use App\Support\ScreenshotMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class DetectScreenshotMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('screenshot.enabled', true) && ScreenshotMode::shouldActivate($request)) {
            ScreenshotMode::activate();

            // Zajištění absolutních URL pro assety
            // Pokud je v screenshot režimu, vynutíme APP_URL jako základ pro asset() a route()
            // Laravel to obvykle dělá, ale pro jistotu v headless režimu to můžeme pojistit
            URL::forceRootUrl(config('app.url'));

            // Pokud používáme Vite a chceme absolutní URL, můžeme zkusit ovlivnit manifest path
            // Ale standardní asset() by měl stačit, pokud je forceRootUrl nastaveno.
        }

        return $next($request);
    }
}
