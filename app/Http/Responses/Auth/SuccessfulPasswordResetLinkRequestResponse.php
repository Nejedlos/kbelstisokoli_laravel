<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as Contract;

class SuccessfulPasswordResetLinkRequestResponse implements Contract
{
    protected $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => __('passwords.sent')], 200)
            : back()->with('status', __('passwords.sent'));
    }
}
