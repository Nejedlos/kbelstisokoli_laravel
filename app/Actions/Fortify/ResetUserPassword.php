<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        \Illuminate\Support\Facades\Log::info('User password reset via Fortify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'password_length' => strlen($input['password']),
        ]);

        $user->forceFill([
            'password' => $input['password'],
            'remember_token' => Str::random(60),
            'onboarding_completed_at' => $user->onboarding_completed_at ?? now(),
        ])->save();

        \Illuminate\Support\Facades\Log::info('Password hash after save', [
            'user_id' => $user->id,
            'hash_prefix' => substr($user->password, 0, 10),
            'check_ok' => Hash::check($input['password'], $user->password),
        ]);

        event(new PasswordReset($user));
    }
}
