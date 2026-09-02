<?php

namespace App\Http\Responses;

use Laravel\Fortify\Http\Responses\PasswordConfirmedResponse as BaseResponse;

class PasswordConfirmedResponse extends BaseResponse
{
    public function toResponse($request)
    {
        if (! $request->wantsJson() && $request->session()->pull('auth.two_factor_return')) {
            return redirect()->route('auth.two-factor-setup');
        }

        return parent::toResponse($request);
    }
}
