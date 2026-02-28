<?php

namespace App\Http\Middleware;

use App\Enums\MembershipStatus;
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
            $isInactive = !$user->is_active;

            \Illuminate\Support\Facades\Log::debug('EnsureUserIsActive.check', [
                'user_id' => $user->id,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'impersonated_by' => $request->session()->get('impersonated_by'),
            ]);
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

                return redirect()->route('login')
                    ->withErrors(['email' => __('Váš účet byl deaktivován. Kontaktujte prosím správce.')]);
            }
        } else {
            \Illuminate\Support\Facades\Log::debug('EnsureUserIsActive.not_authenticated', [
                'path' => $request->path(),
                'session_id' => $request->session()->getId(),
            ]);
        }

        return $next($request);
    }
}
