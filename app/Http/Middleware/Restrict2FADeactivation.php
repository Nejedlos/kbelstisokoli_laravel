<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Restrict2FADeactivation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->canAccessAdmin()) {
            if ($request->routeIs('two-factor.disable')) {
                abort(403, __('Deaktivace dvoufázového ověření není pro administrátory povolena.'));
            }
        }

        return $next($request);
    }
}
