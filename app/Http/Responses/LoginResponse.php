<?php

namespace App\Http\Responses;

use App\Jobs\GenerateUserIdentifiersJob;
use App\Support\AuthRedirect;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements FilamentLoginResponseContract, LoginResponseContract
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();
        $email = $user ? $user->email : 'null';

        \Illuminate\Support\Facades\Log::info('LoginResponse.enter', [
            'user_id' => $user?->id,
            'email' => $email,
            'session_id' => \Illuminate\Support\Facades\Session::getId(),
        ]);

        if ($user && (empty($user->club_member_id) || empty($user->payment_vs))) {
            GenerateUserIdentifiersJob::dispatch($user->id);
        }

        if ($user && $user->canAccessAdmin()) {
            $needsConfirmation = \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication();
            $hasSecret = (bool) $user->two_factor_secret;
            $isConfirmed = (bool) $user->two_factor_confirmed_at;

            if (! $hasSecret || ($needsConfirmation && ! $isConfirmed)) {
                if (! session()->has('url.intended')) {
                    AuthRedirect::storeIntendedUrl(url()->previous());
                }

                \Illuminate\Support\Facades\Log::info('LoginResponse.redirect_to_2fa_setup', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);

                return redirect()->route('auth.two-factor-setup');
            }

            // Po zadání hesla na login stránce VŽDY vyžadujeme kód 2FA,
            // pokud má uživatel 2FA aktivované. Ignorujeme případný starý příznak v session,
            // abychom předešli probliknutí administrace (přímý redirect na challenge).
            $hasValidSession2fa = false;

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
                \Illuminate\Support\Facades\Log::info('LoginResponse.redirect_to_2fa_challenge', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);

                session()->put('login.id', $user->id);

                if (! session()->has('url.intended')) {
                    AuthRedirect::storeIntendedUrl(url()->previous());
                }

                return redirect()->route('two-factor.login');
            }
        }

        $targetUrl = AuthRedirect::getTargetUrl($user, $request);

        \Illuminate\Support\Facades\Log::info('LoginResponse.redirect_to_target', [
            'user_id' => $user?->id,
            'target_url' => $targetUrl,
        ]);

        return redirect()->to($targetUrl);
    }
}
