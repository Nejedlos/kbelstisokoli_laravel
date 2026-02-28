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

        \Illuminate\Support\Facades\Log::info('TwoFactorSetupController.enter', [
            'user_id' => $user?->id,
            'email' => $user?->email,
            'can_access_admin' => $user?->canAccessAdmin(),
            'has_secret' => (bool) ($user?->two_factor_secret),
            'confirmed' => (bool) ($user?->two_factor_confirmed_at),
            'session_id' => \Illuminate\Support\Facades\Session::getId(),
        ]);

        // 2FA setup je vyžadován pouze pro ty, kteří mají přístup do adminu
        if (! $user->canAccessAdmin()) {
            \Illuminate\Support\Facades\Log::info('TwoFactorSetupController.deny_not_admin', [
                'user_id' => $user?->id,
                'email' => $user?->email,
            ]);

            return redirect()->to(config('fortify.home', '/clenska-sekce/dashboard'));
        }

        // Pokud už má 2FA aktivní a potvrzené, není co nastavovat -> redirect do adminu
        $isConfirmed = $user->two_factor_confirmed_at !== null;
        $needsConfirmation = \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication();

        // Pokud už má 2FA aktivní a potvrzené, redirect do adminu
        // ALE pokud jsme právě potvrdili (status v session), necháme ho zobrazit recovery kódy
        if ($user->two_factor_secret && (! $needsConfirmation || $isConfirmed)) {
            if (session('status') !== 'two-factor-authentication-confirmed') {
                \Illuminate\Support\Facades\Log::info('TwoFactorSetupController.already_confirmed_redirect', [
                    'user_id' => $user?->id,
                    'email' => $user?->email,
                ]);

                return redirect()->intended(config('filament.panels.admin.path', 'admin'));
            }
        }

        return view('auth.two-factor-setup', [
            'user' => $user,
            'isConfirmed' => $isConfirmed,
        ]);
    }
}
