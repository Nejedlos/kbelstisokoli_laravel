<?php

namespace App\Actions\Fortify;

use App\Http\Responses\TwoFactorLoginResponse;
use App\Services\Auth\TwoFactorService;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseAction;

class RedirectIfTwoFactorAuthenticatable extends BaseAction
{
    protected function twoFactorChallengeResponse($request, $user)
    {
        $request->session()->put('auth.password_confirmed_at', now()->timestamp);
        $request->session()->forget(['auth.2fa_confirmed_at', 'auth.2fa_fingerprint']);

        if (app(TwoFactorService::class)->rememberDevice($request, $user)) {
            $this->guard->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return app(TwoFactorLoginResponse::class)->toResponse($request);
        }

        return parent::twoFactorChallengeResponse($request, $user);
    }
}
