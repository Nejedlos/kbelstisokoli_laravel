<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactorTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        \Illuminate\Support\Facades\Log::info('CheckTwoFactorTimeout.enter', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
        ]);

        // Pokud uživatel není přihlášen, neřešíme (vyřeší auth middleware)
        if (! $user) {
            return $next($request);
        }

        // Pokud uživatel nemá 2FA aktivované, neřešíme (vyřeší EnsureTwoFactorEnabled pokud je potřeba)
        if (! $user->two_factor_secret) {
            return $next($request);
        }

        // Pokud je impersonace aktivní nebo jsme v screenshot režimu, přeskakujeme kontrolu timeoutu 2FA
        if ($request->session()->has('impersonated_by') || \App\Support\ScreenshotMode::isActive()) {
            return $next($request);
        }

        // Kontrola 2FA potvrzení v session (např. timeout 1 týden)
        $confirmedAt = $request->session()->get('auth.2fa_confirmed_at');
        $timeout = config('auth.2fa_timeout', 604800); // Zvýšeno na 7 dní z 24h pro lepší UX

        if ($confirmedAt && (now()->timestamp - $confirmedAt) < $timeout) {
            return $next($request);
        }

        // Kontrola 2FA "Zapamatovat zařízení" cookie (30 dní)
        $rememberCookie = $request->cookie('2fa_remember');
        if ($rememberCookie) {
            try {
                // Laravel automaticky dešifruje cookies, pokud nejsou v 'except' v EncryptCookies.
                // Naše cookie '2fa_remember' je pole, které Cookie::make serializovalo.
                $data = $rememberCookie;
                
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                
                if (is_array($data) && isset($data['user_id']) && (int) $data['user_id'] === (int) $user->id) {
                    // Zařízení je zapamatováno, prodloužíme platnost potvrzení v session
                    // a zajistíme přítomnost password hashe pro Filament
                    $guard = auth()->getDefaultDriver();
                    $request->session()->put([
                        'auth.2fa_confirmed_at' => now()->timestamp,
                        "password_hash_{$guard}" => $user->getAuthPassword(),
                    ]);

                    return $next($request);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('CheckTwoFactorTimeout.invalid_remember_cookie', ['user_id' => $user->id]);
                // Neplatná cookie, pokračujeme k 2FA challenge
            }
        }

        // Pokud uživatel už je v procesu 2FA challenge, nezasahujeme
        if ($request->routeIs('two-factor.login') || $request->is('two-factor-challenge')) {
            return $next($request);
        }

        // Vynutíme 2FA challenge (Fortify mechanismus)
        // Fortify přesměruje na challenge, pokud je user přihlášen, ale 2FA není v session potvrzeno
        // Musíme ale zneplatnit Fortify 2FA příznak, pokud vypršel náš timeout

        // Uložíme zamýšlenou URL pro návrat po úspěšném 2FA
        \App\Support\AuthRedirect::storeIntendedUrl($request->fullUrl());

        // DŮLEŽITÉ: Fortify challenge potřebuje 'login.id' v session pro identifikaci uživatele,
        // jinak přesměruje zpět na login (což vytvoří redirect loop u přihlášeného uživatele).
        session()->put('login.id', $user->id);

        \Illuminate\Support\Facades\Log::info('CheckTwoFactorTimeout.redirect_to_2fa', [
            'user_id' => $user->id,
            'email' => $user->email,
            'target' => $request->fullUrl(),
        ]);

        return redirect()->route('two-factor.login');
    }
}
