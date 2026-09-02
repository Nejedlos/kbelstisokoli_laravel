<?php

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorService;
use App\Support\AuthRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $twoFactor = app(TwoFactorService::class);

        if (! $user || ! $user->canAccessAdmin() || $user->hasEnabledTwoFactorAuthentication()
            || $twoFactor->isExempt($request) || $twoFactor->isExitRoute($request)
            || $request->routeIs(
                'auth.two-factor-setup', 'two-factor.enable', 'two-factor.confirm',
                'two-factor.qr-code', 'two-factor.secret-key',
                'password.confirm', 'password.confirm.store', 'password.confirmation'
            )) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            AuthRedirect::storeIntendedUrl($request->fullUrl());
        }

        return redirect()->route('auth.two-factor-setup');
    }
}
