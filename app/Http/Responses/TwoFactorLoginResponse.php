<?php

namespace App\Http\Responses;

use App\Support\AuthRedirect;
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
