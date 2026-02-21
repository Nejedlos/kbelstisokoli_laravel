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
        if ($user && $user->can('access_admin')) {
            // A nemá AKTIVOVANÉ (potvrzené) 2FA
            // Fortify používá two_factor_confirmed_at pokud je zapnuté 'confirm' v configu
            $isConfirmed = $user->two_factor_confirmed_at !== null;
            $needsConfirmation = config('fortify.features.two-factor-authentication.confirm') ?? false;

            if (! $user->two_factor_secret || ($needsConfirmation && !$isConfirmed)) {

                // Pokud už není na stránce profilu, přesměrujeme ho tam
                if (! $request->routeIs('member.profile.edit')) {
                    return redirect()->route('member.profile.edit')
                        ->with('error', 'Pro přístup do administrace je vyžadováno AKTIVNÍ dvoufázové ověření (2FA). Prosím, dokončete nastavení ve svém profilu.');
                }
            }
        }

        return $next($request);
    }
}
