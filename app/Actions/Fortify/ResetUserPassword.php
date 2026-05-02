<?php

namespace App\Actions\Fortify;

use App\Models\User;
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

        $user->forceFill([
            'password' => Hash::make($input['password']),
            'remember_token' => Str::random(60),
            'onboarding_completed_at' => $user->onboarding_completed_at ?? now(),
        ])->save();

        \Illuminate\Support\Facades\Log::info('User password reset via Fortify', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
