<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as Contract;
use Illuminate\Support\Facades\Password;

class FailedPasswordResetLinkRequestResponse implements Contract
{
    protected $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function toResponse($request)
    {
        // Pokud uživatel nebyl nalezen, vrátíme neutrální úspěšnou zprávu (anti-enumeration)
        if ($this->status === Password::INVALID_USER) {
            return $request->wantsJson()
                ? new JsonResponse(['message' => __('passwords.sent')], 200)
                : back()->with('status', __('passwords.sent'));
        }

        // Pro ostatní chyby (např. throttled) vrátíme chybu v email poli
        return $request->wantsJson()
            ? new JsonResponse(['errors' => ['email' => [__($this->status)]]], 422)
            : back()->withErrors(['email' => __($this->status)]);
    }
}
