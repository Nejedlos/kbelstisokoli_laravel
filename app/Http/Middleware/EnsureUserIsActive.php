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
        if (auth()->check()) {
            $user = auth()->user();
            $isInactive = ! $user->is_active;

            if ($isInactive) {
                if ($request->session()->has('impersonated_by')) {
                    \Illuminate\Support\Facades\Log::info('EnsureUserIsActive.impersonated_inactive_user', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);

                    return $next($request);
                }

                \Illuminate\Support\Facades\Log::warning('EnsureUserIsActive.account_inactive', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                ]);

                auth()->logout();

                $loginRoute = 'login';
                if ($request->is('admin') || $request->is('admin/*')) {
                    $loginRoute = 'filament.admin.auth.login';
                }

                return redirect()->route($loginRoute)
                    ->withErrors(['email' => __('Váš účet není aktivní. Kontaktujte prosím tým pro aktivaci.')]);
            }
        }

        return $next($request);
    }
}
