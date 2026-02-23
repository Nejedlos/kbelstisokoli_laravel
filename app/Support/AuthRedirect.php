<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Centrální logika pro rozhodování o přesměrování po přihlášení a 2FA.
 */
class AuthRedirect
{
    /**
     * Vrátí cílovou URL po úspěšném přihlášení nebo 2FA.
     *
     * @param User $user
     * @param Request|null $request
     * @return string
     */
    public static function getTargetUrl(User $user, ?Request $request = null): string
    {
        $isAdmin = $user->canAccessAdmin();
        $adminPath = config('filament.panels.admin.path', 'admin');
        $adminPath = str_starts_with($adminPath, '/') ? $adminPath : '/' . $adminPath;

        $memberDashboard = '/clenska-sekce/dashboard';

        // Výchozí fallback dle role
        $fallback = $isAdmin ? $adminPath : $memberDashboard;

        // Získáme intended URL ze session
        $intended = \Illuminate\Support\Facades\Session::get('url.intended');

        \Illuminate\Support\Facades\Log::info('AuthRedirect.start', [
            'user_id' => $user->id,
            'is_admin' => $isAdmin,
            'admin_path' => $adminPath,
            'fallback' => $fallback,
            'intended' => $intended,
            'session_id' => \Illuminate\Support\Facades\Session::getId(),
        ]);

        if ($intended) {
            // 1. Nikdy se nevracet na login/logout/2FA stránky
            if (Str_contains_any($intended, ['/login', '/logout', '/two-factor'])) {
                \Illuminate\Support\Facades\Log::info('AuthRedirect.ignore_auth_path', ['intended' => $intended]);
                \Illuminate\Support\Facades\Session::forget('url.intended');
                return $fallback;
            }

            // 2. Pokud je ne-admin a intended vede do administrace, ignoruj intended
            if (!$isAdmin && str_contains($intended, $adminPath)) {
                \Illuminate\Support\Facades\Log::info('AuthRedirect.non_admin_trying_admin', ['intended' => $intended]);
                \Illuminate\Support\Facades\Session::forget('url.intended');
                return $memberDashboard;
            }

            // 3. Pokud je admin a intended vede na obecný dashboard členské sekce,
            // preferujeme administraci (pokud to nebyla specifická podstránka v členské sekci)
            if ($isAdmin && ($intended === url($memberDashboard) || str_contains($intended, $memberDashboard . '/index') || $intended === url('/'))) {
                \Illuminate\Support\Facades\Log::info('AuthRedirect.admin_preferring_admin', ['intended' => $intended]);
                \Illuminate\Support\Facades\Session::forget('url.intended');
                return $adminPath;
            }

            \Illuminate\Support\Facades\Log::info('AuthRedirect.using_intended', ['intended' => $intended]);
            return $intended;
        }

        \Illuminate\Support\Facades\Log::info('AuthRedirect.using_fallback', ['fallback' => $fallback]);
        return $fallback;
    }
}

/**
 * Pomocná funkce pro kontrolu více podřetězců.
 */
function Str_contains_any(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }
    return false;
}
