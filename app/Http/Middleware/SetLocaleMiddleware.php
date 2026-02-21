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
        $locale = session('locale', $request->cookie('filament_language_switch_locale', config('app.locale')));

        if ($request->has('lang')) {
            $newLocale = $request->get('lang');
            if (in_array($newLocale, ['cs', 'en'])) {
                $locale = $newLocale;
                session(['locale' => $locale]);
                cookie()->queue(cookie()->forever('filament_language_switch_locale', $locale));
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
