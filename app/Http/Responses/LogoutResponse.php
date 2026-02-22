<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract, FilamentLogoutResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        // Zrušíme session a vygenerujeme nový CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Vymažeme 2FA remember cookie
        $cookie = Cookie::forget('2fa_remember');

        return redirect()->to('/')
            ->withCookie($cookie);
    }
}
