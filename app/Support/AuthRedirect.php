<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Centrální logika pro rozhodování o přesměrování po přihlášení a 2FA.
 */
class AuthRedirect
{
    /**
     * Vrátí cílovou URL po úspěšném přihlášení nebo 2FA.
     */
    public static function getTargetUrl(?User $user, ?Request $request = null): string
    {
        if (! $user) {
            return '/admin/login';
        }

        $isAdmin = $user->canAccessAdmin();
        $adminPath = config('filament.panels.admin.path', 'admin');
        $adminPath = str_starts_with($adminPath, '/') ? $adminPath : '/'.$adminPath;

        $memberDashboard = '/clenska-sekce/dashboard';

        $fallback = $isAdmin ? $adminPath : $memberDashboard;

        $intended = \Illuminate\Support\Facades\Session::get('url.intended');

        if ($intended) {
            if (Str_contains_any($intended, ['/login', '/logout', '/two-factor'])) {
                \Illuminate\Support\Facades\Session::forget('url.intended');

                return $fallback;
            }

            if (! $isAdmin && str_contains($intended, $adminPath)) {
                \Illuminate\Support\Facades\Session::forget('url.intended');

                return $memberDashboard;
            }

            if ($isAdmin && ($intended === url($memberDashboard) || str_contains($intended, $memberDashboard.'/index') || $intended === url('/'))) {
                \Illuminate\Support\Facades\Session::forget('url.intended');

                return $adminPath;
            }

            return $intended;
        }

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
