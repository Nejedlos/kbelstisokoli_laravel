<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements FilamentLogoutResponseContract, LogoutResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        // Zrušíme session a vygenerujeme nový CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Poznámka: 2FA remember cookie NEMAZAT, aby vydržela 30 dní i po logoutu (dle požadavku uživatele)

        return redirect()->route('logout.success');
    }
}
