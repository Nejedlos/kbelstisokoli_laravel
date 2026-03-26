<?php

namespace App\Http\Middleware;

use App\Support\ScreenshotMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $userId = $request->query('screenshot_user_id');
            ScreenshotMode::activate($userId);

            // Zajištění absolutních URL pro assety
            URL::forceRootUrl(config('app.url'));

            // Autorizace impersonifikace:
            // Musí existovat buď platná signatura v URL, nebo platný tajný interní token v hlavičce.
            // Samotná hlavička X-Screenshot-Mode: 1 nebo query ?screenshot=1 aktivuje režim (CSS optimalizace),
            // ale NEDOVOLUJE přihlášení uživatele bez důkazu autenticity.
            if ($userId) {
                $internalToken = config('screenshot.internal_token');
                $isAuthenticated = ($internalToken && $request->header('X-Screenshot-Token') === $internalToken)
                                   || $request->hasValidSignature();

                if ($isAuthenticated) {
                    try {
                        // Přihlášení uživatele pro tento request (impersonifikace)
                        // Funguje pro web i Filament sekce, pokud je StartSession middleware už zpracován.
                        Auth::loginUsingId($userId);
                    } catch (\Throwable $e) {
                        // Tichý fail, pokud Auth systém ještě není inicializován
                    }
                }
            }
        }

        return $next($request);
    }
}
