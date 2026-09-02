<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidTwoFactorChallenge
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::find($request->session()->get('login.id'));

        if (! $user || ! $user->is_active || ! $user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->forget(['login.id', 'login.remember']);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
