<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse as FilamentPasswordResetResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetResponse implements FilamentPasswordResetResponseContract, PasswordResetResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        // Pokud po resetu hesla (např. přes Filament page) není uživatel přihlášen,
        // zkusíme ho identifikovat podle e-mailu z requestu a přihlásit,
        // abychom mohli využít centrální LoginResponse (včetně vynucení 2FA setupu/challenge).
        if (! auth()->check()) {
            $email = $request->input('email') ?: $request->input('data.email');

            if ($email) {
                $email = trim(strtolower($email));
                $user = User::where('email', $email)->first();
                if ($user) {
                    auth()->login($user);
                    Log::info('PasswordResetResponse.auto_login_successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'can_access_admin' => $user->canAccessAdmin(),
                    ]);
                }
            }
        }

        // Chceme použít stejnou logiku přesměrování (včetně 2FA checku) jako při běžném loginu.
        return app(LoginResponse::class)->toResponse($request);
    }
}
