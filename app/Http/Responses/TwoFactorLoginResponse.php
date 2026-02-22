<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        $user = auth()->user();

        // Nastavíme čas potvrzení 2FA v session pro timeout (např. 24 hodin)
        $request->session()->put('auth.2fa_confirmed_at', now()->timestamp);

        // Fallback target
        $isAdmin = $user->can('access_admin') || $user->hasRole('admin');
        $adminPath = config('filament.panels.admin.path', 'admin');
        $adminPath = str_starts_with($adminPath, '/') ? $adminPath : '/' . $adminPath;

        $fallback = $isAdmin
            ? $adminPath
            : config('fortify.home', '/clenska-sekce/dashboard');

        // Pokud je v session intended URL, redirect->intended ji použije.
        $intended = session()->get('url.intended');

        // Pokud je uživatel admin a směřuje do členské sekce (výchozí home),
        // přebijeme to administrací, aby neskončil v členské sekci.
        if ($isAdmin && $intended && (str_contains($intended, '/clenska-sekce/dashboard') || $intended === url('/clenska-sekce/dashboard'))) {
            session()->forget('url.intended');
        }

        // Důležité: Pokud admin uživatel loguje přes /login (nemá intended),
        // fallback ho nyní správně pošle do /admin.
        $response = redirect()->intended($fallback);

        // Pokud uživatel zaškrtl "Zapamatovat zařízení", nastavíme cookie na 30 dní
        if ($request->remember_device) {
            $token = bin2hex(random_bytes(32));

            // Uložíme token do user metadata nebo session (pro jednoduchost zde použijeme podepsanou cookie s user_id)
            $cookie = \Illuminate\Support\Facades\Cookie::make(
                '2fa_remember',
                encrypt(['user_id' => $user->id, 'token' => $token]),
                30 * 24 * 60 // 30 dní v minutách
            );
            $response->withCookie($cookie);
        }

        return $response;
    }
}
