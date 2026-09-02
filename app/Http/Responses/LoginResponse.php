<?php

namespace App\Http\Responses;

use App\Jobs\GenerateUserIdentifiersJob;
use App\Services\Auth\TwoFactorService;
use App\Support\AuthRedirect;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements FilamentLoginResponseContract, LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = $request->user();
        $twoFactor = app(TwoFactorService::class);

        if ($user && (empty($user->club_member_id) || empty($user->payment_vs))) {
            GenerateUserIdentifiersJob::dispatch($user->id);
        }

        if ($user) {
            $request->session()->forget(['auth.2fa_confirmed_at', 'auth.2fa_fingerprint']);
            // This response follows successful password verification, including password reset.
            $request->session()->put('auth.password_confirmed_at', now()->timestamp);

            if ($user->canAccessAdmin() && ! $user->hasEnabledTwoFactorAuthentication()) {
                return redirect()->route('auth.two-factor-setup');
            }

            if ($user->hasEnabledTwoFactorAuthentication() && ! $twoFactor->rememberDevice($request, $user)) {
                return $twoFactor->challenge($request, $user, (bool) $request->session()->pull('login.remember', $request->boolean('remember')));
            }
        }

        return redirect()->to(AuthRedirect::getTargetUrl($user, $request));
    }
}
