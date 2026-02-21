<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as FilamentLoginResponseContract;
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

        // 1. Intended URL (pokud je bezpečné a existuje)
        if ($intended = session()->get('url.intended')) {
            // Jen pokud intended není login/logout nebo jiná systémová route, kterou nechceme
            if (!str_contains($intended, '/login') && !str_contains($intended, '/logout')) {
                return redirect()->intended($intended);
            }
        }

        // 2. Role-based redirect
        if ($user->hasAnyRole(['admin', 'editor', 'coach'])) {
            // Admin sekce
            return redirect()->to(config('filament.panels.admin.path', 'admin'));
        }

        // 3. Member sekce (default)
        return redirect()->to(config('fortify.home', '/clenska-sekce/dashboard'));
    }
}
