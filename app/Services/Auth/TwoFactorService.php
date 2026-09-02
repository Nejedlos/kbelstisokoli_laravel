<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Livewire\Features\SupportRedirects\Redirector;

class TwoFactorService
{
    public function isExempt(Request $request): bool
    {
        // Impersonation is entered through a route protected by both factors.
        // A screenshot query parameter or header alone is never authentication.
        return $request->session()->has('impersonated_by')
            || $request->attributes->get('two_factor_trusted_screenshot', false);
    }

    public function isExitRoute(Request $request): bool
    {
        return $request->routeIs('logout', 'admin.logout', 'filament.admin.auth.logout', 'logout.success', 'language.switch');
    }

    public function fingerprint(User $user): string
    {
        return hash_hmac('sha256', $user->getKey().'|'.$user->two_factor_secret.'|'.$user->getAuthPassword(), config('app.key'));
    }

    public function confirm(Request $request, User $user): void
    {
        $guard = config('fortify.guard', 'web');
        $request->session()->put([
            'auth.2fa_confirmed_at' => now()->timestamp,
            'auth.2fa_fingerprint' => $this->fingerprint($user),
            "password_hash_{$guard}" => $user->getAuthPassword(),
        ]);
        $request->session()->forget(['login.id', 'login.remember']);
    }

    public function isConfirmed(Request $request, User $user): bool
    {
        $confirmedAt = $request->session()->get('auth.2fa_confirmed_at', 0);

        return $confirmedAt > 0 && $confirmedAt <= now()->timestamp
            && now()->timestamp - $confirmedAt < config('auth.2fa_timeout', 2592000)
            && hash_equals($this->fingerprint($user), (string) $request->session()->get('auth.2fa_fingerprint', ''));
    }

    public function rememberDevice(Request $request, User $user): bool
    {
        $data = json_decode((string) $request->cookie('2fa_remember', ''), true);

        if (! is_array($data) || ($data['user_id'] ?? null) !== $user->getKey()
            || ! is_numeric($data['expires_at'] ?? null) || $data['expires_at'] <= now()->timestamp
            || ! is_string($data['fingerprint'] ?? null)
            || ! hash_equals($this->fingerprint($user), $data['fingerprint'])) {
            return false;
        }

        $this->confirm($request, $user);

        return true;
    }

    public function challenge(Request $request, User $user, bool $remember = false): RedirectResponse|Redirector
    {
        if ($request->isMethod('GET')) {
            AuthRedirect::storeIntendedUrl($request->fullUrl());
        }

        $request->session()->forget(['auth.2fa_confirmed_at', 'auth.2fa_fingerprint']);
        auth()->guard(config('fortify.guard', 'web'))->logoutCurrentDevice();
        $request->session()->regenerate();
        $request->session()->put(['login.id' => $user->getKey(), 'login.remember' => $remember]);

        return redirect()->route('two-factor.login');
    }
}
