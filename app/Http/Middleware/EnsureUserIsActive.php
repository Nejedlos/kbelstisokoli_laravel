<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->is_active) {
            \Illuminate\Support\Facades\Log::warning('EnsureUserIsActive.deactivated', [
                'user_id' => auth()->id(),
                'email' => auth()->user()->email,
            ]);

            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Účet je deaktivován. Kontaktujte správce.']);
        }

        return $next($request);
    }
}
