<?php

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTwoFactorTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $twoFactor = app(TwoFactorService::class);

        // An unfinished optional setup must not lock a member out.
        if (! $user || ! $user->hasEnabledTwoFactorAuthentication()
            || $twoFactor->isExempt($request) || $twoFactor->isExitRoute($request)
            || $request->routeIs('two-factor.login', 'two-factor.login.store')
            || $twoFactor->isConfirmed($request, $user) || $twoFactor->rememberDevice($request, $user)) {
            return $next($request);
        }

        return $twoFactor->challenge($request, $user, auth()->viaRemember());
    }
}
