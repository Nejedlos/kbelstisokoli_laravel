<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pokud je uživatel přihlášen a má oprávnění pro přístup do adminu
        // A ZÁROVEŇ se pokouší přistoupit k admin sekci (včetně Filamentu)
        if ($user && $user->can('access_admin') && ($request->is('admin*') || $request->routeIs('admin.*'))) {
            // A nemá AKTIVOVANÉ (potvrzené) 2FA
            // Fortify používá two_factor_confirmed_at pokud je zapnuté 'confirm' v configu
            $isConfirmed = $user->two_factor_confirmed_at !== null;
            $needsConfirmation = config('fortify.features.two-factor-authentication.confirm') ?? false;

            if (! $user->two_factor_secret || ($needsConfirmation && !$isConfirmed)) {

                // Pokud už není na stránce nastavení 2FA, přesměrujeme ho tam
                if (! $request->routeIs('auth.two-factor-setup')) {
                    return redirect()->route('auth.two-factor-setup');
                }
            }
        }

        return $next($request);
    }
}
