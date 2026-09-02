<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuthRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        // Confirm the password before rendering a secret or recovery codes.
        // Keep the final destination separate from the password confirmation return URL.
        if (now()->timestamp - $request->session()->get('auth.password_confirmed_at', 0) > config('auth.password_timeout', 10800)) {
            $request->session()->put('auth.two_factor_return', true);

            return redirect()->route('password.confirm');
        }

        return view('auth.two-factor-setup', [
            'user' => $request->user(),
            'isConfirmed' => $request->user()->hasEnabledTwoFactorAuthentication(),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        if (! $request->user()->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('auth.two-factor-setup');
        }

        return redirect()->to(AuthRedirect::getTargetUrl($request->user(), $request));
    }
}
