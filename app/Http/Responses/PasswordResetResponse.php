<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse as FilamentPasswordResetResponseContract;
use Illuminate\Http\RedirectResponse;
use App\Http\Responses\LoginResponse;

class PasswordResetResponse implements PasswordResetResponseContract, FilamentPasswordResetResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Pokud po resetu hesla (např. přes Filament page) není uživatel přihlášen,
        // zkusíme ho identifikovat podle e-mailu z requestu a přihlásit,
        // abychom mohli využít centrální LoginResponse (včetně vynucení 2FA setupu/challenge).
        if (! auth()->check()) {
            $email = $request->input('email') ?: $request->input('data.email');

            if ($email) {
                $user = \App\Models\User::where('email', $email)->first();
                if ($user) {
                    auth()->login($user);
                    \Illuminate\Support\Facades\Log::info('PasswordResetResponse.auto_login_successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                }
            }
        }

        // Chceme použít stejnou logiku přesměrování (včetně 2FA checku) jako při běžném loginu.
        return app(LoginResponse::class)->toResponse($request);
    }
}
