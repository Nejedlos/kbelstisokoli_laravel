<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['cs', 'en']);
        $defaultLocale = config('app.locale', 'cs');

        // 1. Zjistíme locale (Query > Session > Cookie > Config)
        $locale = $request->query('lang');

        if ($locale && in_array($locale, $supportedLocales)) {
            // Pokud je v query, nastavíme ho do session i cookie pro příště
            session(['locale' => $locale]);
            cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));

            // REDIRECT na čistou URL bez lang parametru (proti "traps" při zpětném odkazu a pro SEO)
            $url = $request->fullUrlWithQuery(['lang' => null]);
            return redirect($url);
        } else {
            // Jinak zkusíme session
            $locale = session('locale');

            // Pokud není v session, zkusíme cookie
            if (! $locale) {
                $locale = $request->cookie('filament_language_switch_locale');
            }

            // Fallback na výchozí
            if (! $locale || ! in_array($locale, $supportedLocales)) {
                $locale = $defaultLocale;
            }
        }

        // 2. Aplikujeme locale
        app()->setLocale($locale);
        config(['app.locale' => $locale]);

        // 3. Synchronizujeme session i cookie (pokud se liší od aktuálně zjištěného locale)
        if (session()->isStarted() && session('locale') !== $locale) {
            session(['locale' => $locale]);
        }

        if ($request->cookie('filament_language_switch_locale') !== $locale) {
            cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
        }

        return $next($request);
    }
}
