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
     * Bezpečně uloží cílovou URL do session, pokud splňuje podmínky.
     */
    public static function storeIntendedUrl(?string $url): void
    {
        if (! $url || ! is_string($url)) {
            return;
        }

        // Ignorujeme URL, které jsou příliš dlouhé (prevence DoS přes session)
        if (strlen($url) > 2048) {
            return;
        }

        // Musí jít o interní URL aplikace (prevence open redirect)
        if (! self::isInternalUrl($url)) {
            \Illuminate\Support\Facades\Log::debug('AuthRedirect: External URL ignored', ['url' => $url]);
            return;
        }

        // Vyloučit auth-related stránky, u kterých návrat nedává smysl
        if (Str_contains_any($url, [
            '/login',
            '/logout',
            '/two-factor',
            '/2fa-challenge',
            '/password-reset',
            '/reset-password',
            '/email-verification',
            '/logout-success',
            '/register'
        ])) {
            \Illuminate\Support\Facades\Log::debug('AuthRedirect: Auth-related URL ignored', ['url' => $url]);
            return;
        }

        \Illuminate\Support\Facades\Log::debug('AuthRedirect: Storing intended URL', ['url' => $url]);
        \Illuminate\Support\Facades\Session::put('url.intended', $url);
    }

    /**
     * Zkontroluje, zda jde o interní URL (stejná doména nebo relativní cesta).
     */
    public static function isInternalUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        $appUrl = config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost === null) {
            return false;
        }

        // Porovnáme hosty (case-insensitive)
        return strtolower($urlHost) === strtolower($appHost);
    }

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
            // VŽDY vyčistíme intended URL po přečtení (one-time use)
            \Illuminate\Support\Facades\Session::forget('url.intended');

            \Illuminate\Support\Facades\Log::debug('AuthRedirect: Processing intended URL', [
                'intended' => $intended,
                'user_id' => $user->id,
                'is_admin' => $isAdmin
            ]);

            // Dodatečná validace po načtení (pro případ, že by byla v session uložena nevalidní URL)
            if (Str_contains_any($intended, ['/login', '/logout', '/two-factor', '/2fa-challenge'])) {
                \Illuminate\Support\Facades\Log::debug('AuthRedirect: Intended URL is auth-related, ignoring', ['intended' => $intended]);
                return $fallback;
            }

            // Ochrana oprávnění: non-admin nesmí do adminu, i když to má jako intended
            if (! $isAdmin && str_contains($intended, $adminPath)) {
                \Illuminate\Support\Facades\Log::debug('AuthRedirect: Non-admin tried to access admin path, redirecting to member dashboard');
                return $memberDashboard;
            }

            // Pokud admin přijde z domovské stránky, pošleme ho raději do dashboardu
            if ($isAdmin && ($intended === url('/') || $intended === route('public.home'))) {
                \Illuminate\Support\Facades\Log::debug('AuthRedirect: Admin coming from home page, redirecting to admin dashboard');
                return $adminPath;
            }

            // Musí jít o interní URL
            if (! self::isInternalUrl($intended)) {
                \Illuminate\Support\Facades\Log::warning('AuthRedirect: External intended URL detected and blocked', ['intended' => $intended]);
                return $fallback;
            }

            \Illuminate\Support\Facades\Log::info('AuthRedirect: Redirecting to intended URL', ['intended' => $intended]);

            return $intended;
        }

        \Illuminate\Support\Facades\Log::debug('AuthRedirect: No intended URL, using fallback', ['fallback' => $fallback]);

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
