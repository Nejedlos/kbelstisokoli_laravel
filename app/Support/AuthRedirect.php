<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
            Log::debug('AuthRedirect: External URL ignored', ['url' => $url]);

            return;
        }

        // Vyloučit auth-related stránky, u kterých návrat nedává smysl
        if (Str_contains_any($url, [
            '/login',
            '/logout',
            '/two-factor',
            '/2fa-challenge',
            '/password-reset',
            '/confirm-password',
            '/confirmed-password',
            '/reset-password',
            '/email-verification',
            '/logout-success',
            '/register',
            '/feedback/widget',
        ])) {
            Log::debug('AuthRedirect: Auth-related URL ignored', ['url' => $url]);

            return;
        }

        Log::debug('AuthRedirect: Storing intended URL', ['url' => $url]);
        Session::put('url.intended', $url);
    }

    /**
     * Zkontroluje, zda jde o interní URL (stejná doména nebo relativní cesta).
     */
    public static function isInternalUrl(string $url): bool
    {
        if (preg_match('/[\x00-\x20\\\\]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//');
        }

        $appUrl = config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if (! $appHost || ! $urlHost || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)
            || parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_PASS) !== null) {
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

        // Logika pro prioritní přesměrování:
        // Všechny uživatele (včetně adminů) posíláme primárně do členské sekce,
        // pokud nemají explicitní záměr jít do administrace (url.intended).
        $fallback = $isAdmin && ! $user->can('view_member_section') ? $adminPath : $memberDashboard;

        $intended = Session::get('url.intended');

        if ($intended) {
            // VŽDY vyčistíme intended URL po přečtení (one-time use)
            Session::forget('url.intended');

            Log::debug('AuthRedirect: Processing intended URL', [
                'intended' => $intended,
                'user_id' => $user->id,
                'is_admin' => $isAdmin,
            ]);

            // Dodatečná validace po načtení:
            // 1. Vyloučit auth-related stránky
            // 2. Pokud admin/člen přijde z domovské stránky, pošleme ho raději do dashboardu,
            //    protože pro přihlášeného uživatele je dashboard užitečnější startovní bod.
            $isHome = $intended === url('/') || $intended === route('public.home') || $intended === '/';

            if ($isHome || Str_contains_any($intended, ['/login', '/logout', '/two-factor', '/2fa-challenge', '/feedback/widget', '/confirm-password', '/confirmed-password', '/reset-password', '/password-reset', '/confirmed-two-factor'])) {
                Log::debug('AuthRedirect: Intended URL is home or auth-related, using fallback', ['intended' => $intended, 'fallback' => $fallback]);

                return $fallback;
            }

            if ($isAdmin && ! $user->can('view_member_section') && str_contains($intended, '/clenska-sekce')) {
                return $adminPath;
            }

            // Ochrana oprávnění: non-admin nesmí do adminu, i když to má jako intended
            if (! $isAdmin && str_contains($intended, $adminPath)) {
                Log::debug('AuthRedirect: Non-admin tried to access admin path, redirecting to member dashboard');

                return $memberDashboard;
            }

            // Musí jít o interní URL
            if (! self::isInternalUrl($intended)) {
                Log::warning('AuthRedirect: External intended URL detected and blocked', ['intended' => $intended]);

                return $fallback;
            }

            Log::info('AuthRedirect: Redirecting to intended URL', ['intended' => $intended]);

            return $intended;
        }

        Log::debug('AuthRedirect: No intended URL, using fallback', ['fallback' => $fallback]);

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
