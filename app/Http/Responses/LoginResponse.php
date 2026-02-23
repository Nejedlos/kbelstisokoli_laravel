<?php

namespace App\Http\Responses;

use App\Support\AuthRedirect;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract, FilamentLoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
     */
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        // --- Hardened admin 2FA flow ---
        // Pokud má uživatel přístup do adminu, zajistíme, že 2FA challenge/setting proběhne
        // ještě před tím, než ho pošleme do adminu. Tím se vyhneme možným kolizím
        // s jinými middleware během přístupu na /admin.
        if ($user && $user->canAccessAdmin()) {
            $needsConfirmation = \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication();
            $hasSecret = (bool) $user->two_factor_secret;
            $isConfirmed = (bool) $user->two_factor_confirmed_at;

            // 1) Nemá 2FA nastavené / potvrzené -> redirect na setup
            if (! $hasSecret || ($needsConfirmation && ! $isConfirmed)) {
                \Illuminate\Support\Facades\Log::info('LoginResponse.redirect_to_2fa_setup', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                // Pro konzistentní návrat po setupu uložíme intended URL, pokud ještě není
                if (! session()->has('url.intended')) {
                    session()->put('url.intended', url(config('filament.panels.admin.path', 'admin')));
                }

                return redirect()->route('auth.two-factor-setup');
            }

            // 2) 2FA je aktivní, ale v této session zatím nepotvrzené (timeout / první login)
            $confirmedAt = $request->session()->get('auth.2fa_confirmed_at');
            $timeout = (int) config('auth.2fa_timeout', 86400);
            $hasValidSession2fa = $confirmedAt && (now()->timestamp - $confirmedAt) < $timeout;

            // „Zapamatovat zařízení" cookie
            $rememberCookie = $request->cookie('2fa_remember');
            $remembered = false;
            if ($rememberCookie) {
                try {
                    $data = decrypt($rememberCookie);
                    $remembered = isset($data['user_id']) && $data['user_id'] === $user->id;
                } catch (\Throwable $e) {
                    $remembered = false;
                }
            }

            if (! $hasValidSession2fa && ! $remembered) {
                // Uložíme Fortify identifikátor pro challenge (jinak by Fortify vrátilo zpět na login)
                session()->put('login.id', $user->id);

                // Nastavíme intended cíl na admin, pokud zatím není
                if (! session()->has('url.intended')) {
                    session()->put('url.intended', url(config('filament.panels.admin.path', 'admin')));
                }

                \Illuminate\Support\Facades\Log::info('LoginResponse.redirect_to_2fa_challenge', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                return redirect()->route('two-factor.login');
            }
        }

        // Výchozí proud: po přihlášení pokračujeme na cílovou URL podle centrální logiky
        $targetUrl = AuthRedirect::getTargetUrl($user, $request);

        \Illuminate\Support\Facades\Log::info('LoginResponse.redirect', [
            'user_id' => $user?->id,
            'target_url' => $targetUrl,
        ]);

        return redirect()->to($targetUrl);
    }
}
