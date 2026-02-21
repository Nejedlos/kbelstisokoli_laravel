<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    /**
     * Zobrazí stránku pro nastavení 2FA pro administrátory.
     */
    public function __invoke(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // 2FA setup je vyžadován pouze pro ty, kteří mají přístup do adminu
        if (! $user->can('access_admin')) {
            return redirect()->to(config('fortify.home', '/clenska-sekce/dashboard'));
        }

        // Pokud už má 2FA aktivní a potvrzené, není co nastavovat -> redirect do adminu
        $isConfirmed = $user->two_factor_confirmed_at !== null;
        $needsConfirmation = config('fortify.features.two-factor-authentication.confirm') ?? false;

        if ($user->two_factor_secret && (!$needsConfirmation || $isConfirmed)) {
            return redirect()->intended(config('filament.panels.admin.path', 'admin'));
        }

        return view('auth.two-factor-setup', [
            'user' => $user,
        ]);
    }
}
