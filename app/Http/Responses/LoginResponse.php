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

        if ($user && (empty($user->club_member_id) || empty($user->payment_vs))) {
            GenerateUserIdentifiersJob::dispatch($user->id);
        }

        if ($user && $user->canAccessAdmin()) {
            $needsConfirmation = \Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication();
            $hasSecret = (bool) $user->two_factor_secret;
            $isConfirmed = (bool) $user->two_factor_confirmed_at;

            if (! $hasSecret || ($needsConfirmation && ! $isConfirmed)) {
                if (! session()->has('url.intended')) {
                    session()->put('url.intended', url(config('filament.panels.admin.path', 'admin')));
                }

                return redirect()->route('auth.two-factor-setup');
            }

            $confirmedAt = $request->session()->get('auth.2fa_confirmed_at');
            $timeout = (int) config('auth.2fa_timeout', 86400);
            $hasValidSession2fa = $confirmedAt && (now()->timestamp - $confirmedAt) < $timeout;

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
                session()->put('login.id', $user->id);

                if (! session()->has('url.intended')) {
                    session()->put('url.intended', url(config('filament.panels.admin.path', 'admin')));
                }

                return redirect()->route('two-factor.login');
            }
        }

        $targetUrl = AuthRedirect::getTargetUrl($user, $request);

        return redirect()->to($targetUrl);
    }
}
