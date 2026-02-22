<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Fortify;

class CheckTwoFactorTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pokud uživatel není přihlášen, neřešíme (vyřeší auth middleware)
        if (! $user) {
            return $next($request);
        }

        // Pokud uživatel nemá 2FA aktivované, neřešíme (vyřeší EnsureTwoFactorEnabled pokud je potřeba)
        if (! $user->two_factor_secret) {
            return $next($request);
        }

        // Kontrola 2FA potvrzení v session (např. timeout 24 hodin)
        $confirmedAt = $request->session()->get('auth.2fa_confirmed_at');
        $timeout = config('auth.2fa_timeout', 86400); // Výchozí 24 hodin

        if ($confirmedAt && (now()->timestamp - $confirmedAt) < $timeout) {
            return $next($request);
        }

        // Kontrola 2FA "Zapamatovat zařízení" cookie (30 dní)
        $rememberCookie = $request->cookie('2fa_remember');
        if ($rememberCookie) {
            try {
                $data = decrypt($rememberCookie);
                if (isset($data['user_id']) && $data['user_id'] === $user->id) {
                    // Zařízení je zapamatováno, prodloužíme platnost potvrzení v session
                    $request->session()->put('auth.2fa_confirmed_at', now()->timestamp);
                    return $next($request);
                }
            } catch (\Exception $e) {
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
        session()->put('url.intended', $request->fullUrl());

        return redirect()->route('two-factor.login');
    }
}
