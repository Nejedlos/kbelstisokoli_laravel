<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract, FilamentLoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        $user = auth()->user();

        // Target pro fallback
        $isAdmin = $user->can('access_admin') || $user->hasRole('admin');
        $adminPath = config('filament.panels.admin.path', 'admin');
        $adminPath = str_starts_with($adminPath, '/') ? $adminPath : '/' . $adminPath;

        $fallback = $isAdmin
            ? $adminPath
            : config('fortify.home', '/clenska-sekce/dashboard');

        // Pokud je v session intended URL, Laravel ji použije, jinak fallback.
        // Ale chceme se vyhnout redirectu zpět na login/logout stránky v session.
        $intended = session()->get('url.intended');

        // Pokud je uživatel admin a směřuje do členské sekce (výchozí home),
        // přebijeme to administrací, aby neskončil v členské sekci, pokud tam vyloženě nechtěl na konkrétní podstránku.
        if ($isAdmin && $intended && (str_contains($intended, '/clenska-sekce/dashboard') || $intended === url('/clenska-sekce/dashboard'))) {
            session()->forget('url.intended');
        }

        if ($intended && (str_contains($intended, '/login') || str_contains($intended, '/logout'))) {
            session()->forget('url.intended');
        }

        return redirect()->intended($fallback);
    }
}
