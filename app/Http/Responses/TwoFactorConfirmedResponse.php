<?php

namespace App\Http\Responses;

use App\Services\Auth\TwoFactorService;
use Laravel\Fortify\Http\Responses\TwoFactorConfirmedResponse as BaseResponse;

class TwoFactorConfirmedResponse extends BaseResponse
{
    public function toResponse($request)
    {
        app(TwoFactorService::class)->confirm($request, $request->user());

        return $request->wantsJson()
            ? parent::toResponse($request)
            : redirect()->route('auth.two-factor-setup')->with('status', 'two-factor-authentication-confirmed');
    }
}
