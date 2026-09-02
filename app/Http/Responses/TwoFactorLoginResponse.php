<?php

namespace App\Http\Responses;

use App\Services\Auth\TwoFactorService;
use App\Support\AuthRedirect;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();
        $twoFactor = app(TwoFactorService::class);
        $twoFactor->confirm($request, $user);
        $response = redirect()->to(AuthRedirect::getTargetUrl($user, $request));

        if ($request->boolean('remember_device')) {
            $response->withCookie(Cookie::make('2fa_remember', json_encode([
                'user_id' => $user->getKey(),
                'fingerprint' => $twoFactor->fingerprint($user),
                'expires_at' => now()->addDays(30)->timestamp,
            ]), 30 * 24 * 60));
        }

        return $response;
    }
}
