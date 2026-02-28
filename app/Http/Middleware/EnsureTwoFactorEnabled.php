<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
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
        $isAdminRoute = $request->is('admin*') || $request->routeIs('admin.*') || $request->routeIs('filament.admin.*');

        $isAdmin = $user && $user->canAccessAdmin();

        \Illuminate\Support\Facades\Log::info('EnsureTwoFactorEnabled.handle', [
            'path' => $request->path(),
            'route' => optional($request->route())->getName(),
            'is_admin_route' => $isAdminRoute,
            'user_id' => $user?->id,
            'email' => $user?->email,
            'can_access_admin' => $user?->canAccessAdmin(),
            'has_secret' => (bool) ($user?->two_factor_secret),
            'confirmed' => (bool) ($user?->two_factor_confirmed_at),
            'needs_confirmation' => \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication(),
            'session_id' => \Illuminate\Support\Facades\Session::getId(),
            'impersonated_by' => $request->session()->get('impersonated_by'),
        ]);

        // Pokud je impersonace aktivní, přeskakujeme kontrolu 2FA
        if ($request->session()->has('impersonated_by')) {
            return $next($request);
        }

        if ($isAdmin && $isAdminRoute) {
            // A nemá AKTIVOVANÉ (potvrzené) 2FA
            // Fortify používá two_factor_confirmed_at pokud je zapnuté 'confirm' v configu
            $isConfirmed = $user->two_factor_confirmed_at !== null;
            $needsConfirmation = \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication();

            if (! $user->two_factor_secret || ($needsConfirmation && ! $isConfirmed)) {

                // Pokud už není na stránce nastavení 2FA, přesměrujeme ho tam
                if (! $request->routeIs('auth.two-factor-setup')) {
                    \Illuminate\Support\Facades\Log::info('EnsureTwoFactorEnabled.redirect_to_setup', [
                        'user_id' => $user?->id,
                        'email' => $user?->email,
                    ]);

                    return redirect()->route('auth.two-factor-setup');
                }
            }
        }

        return $next($request);
    }
}
