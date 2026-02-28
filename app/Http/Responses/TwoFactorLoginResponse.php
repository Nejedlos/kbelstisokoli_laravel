<?php

namespace App\Http\Responses;

use App\Support\AuthRedirect;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        $user = auth()->user();

        // Zajistíme přítomnost password hashe v session pro Filament's AuthenticateSession middleware
        $guard = auth()->getDefaultDriver();
        $request->session()->put([
            "password_hash_{$guard}" => $user->getAuthPassword(),
            'auth.2fa_confirmed_at' => now()->timestamp,
        ]);

        \Illuminate\Support\Facades\Log::info('TwoFactorLoginResponse.session_prepared', [
            'user_id' => $user->id,
            'guard' => $guard,
            'has_password_hash' => $request->session()->has("password_hash_{$guard}"),
        ]);

        // Použijeme centrální logiku pro určení cíle
        $targetUrl = AuthRedirect::getTargetUrl($user, $request);

        \Illuminate\Support\Facades\Log::info('TwoFactorLoginResponse.redirect', [
            'user_id' => $user?->id,
            'target_url' => $targetUrl,
        ]);

        $response = redirect()->to($targetUrl);

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
