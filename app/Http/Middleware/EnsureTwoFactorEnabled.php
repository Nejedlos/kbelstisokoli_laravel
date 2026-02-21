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
            // A nemá aktivované 2FA
            if (! $user->two_factor_secret) {
                // Pokud už není na stránce profilu, přesměrujeme ho tam
                if (! $request->routeIs('member.profile.edit')) {
                    return redirect()->route('member.profile.edit')
                        ->with('error', 'Pro přístup do administrace je vyžadováno dvoufázové ověření (2FA). Prosím, aktivujte si jej ve svém profilu.');
                }
            }
        }

        return $next($request);
    }
}
