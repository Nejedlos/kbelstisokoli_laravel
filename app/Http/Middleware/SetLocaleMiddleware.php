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
        \Illuminate\Support\Facades\Log::debug('SetLocaleMiddleware.handle', [
            'path' => $request->path(),
            'method' => $request->method(),
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'session_id' => $request->session()->getId(),
            'impersonated_by' => $request->session()->get('impersonated_by'),
            'referrer' => $request->header('referer'),
        ]);

        $locale = $request->query('lang');

        if ($locale && in_array($locale, ['cs', 'en'])) {
            session(['locale' => $locale]);
            // Synchronizace s cookie, kterou používá Filament i náš middleware jako fallback
            cookie()->queue('filament_language_switch_locale', $locale, 60 * 24 * 365);
        } else {
            $locale = session('locale', $request->cookie('filament_language_switch_locale', config('app.locale')));
        }

        // Validace, aby se tam nedostalo něco nečekaného
        if (!in_array($locale, ['cs', 'en'])) {
            $locale = config('app.locale', 'cs');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
