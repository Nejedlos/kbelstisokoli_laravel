<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class FullPageCacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rychlá kontrola pro hosty
        $isGuest = true;
        try {
            if (auth()->check()) {
                $isGuest = false;
            }
        } catch (\Throwable $e) {
            // Auth zatím není připraven, spoléháme na výchozí true
        }

        // Cache zapnuta pouze pro GET, hosty a pokud je aktivní v configu
        if (! $isGuest || ! $this->shouldCache($request) || $this->isImpersonating($request)) {
            return $next($request);
        }

        // Zahrneme do klíče i query parametry (seřazené) a jazyk
        $queryParams = $request->query();
        ksort($queryParams);
        $locale = app()->getLocale();
        $cacheKey = 'full_page_'.md5($request->path().'_'.serialize($queryParams).'_'.$locale);
        $ttl = config('performance.cache_ttl.full_page', 86400);

        if (Cache::has($cacheKey) && ! $request->hasHeader('X-Prime-Cache')) {
            $cached = Cache::get($cacheKey);

            $response = response($cached['content']);
            $response->headers->set('Content-Type', $cached['type']);
            $response->headers->set('X-Page-Cache', 'hit');

            // Přidáme i cookies, které mohly být vyfrontovány předchozími middleware (např. SetLocale)
            foreach (cookie()->getQueuedCookies() as $cookie) {
                $response->headers->setCookie($cookie);
            }

            return $response;
        }

        $response = $next($request);
        $content = $response->getContent();

        // Podmínky pro uložení do cache:
        // 1. Status OK (200)
        // 2. Obsah není prázdný
        // 3. Nejde o Livewire request (X-Livewire header)
        // 4. Uživatel není přihlášen (ověření po proběhnutí session)
        // 5. Obsah neobsahuje CSRF token (pro jistotu, aby se nenacachovaly formuláře)
        if ($response->getStatusCode() === 200
            && ! empty($content)
            && ! $request->hasHeader('X-Livewire')
            && ! auth()->check()
            && ! str_contains($content, 'name="_token"')
        ) {
            Cache::put($cacheKey, [
                'content' => $content,
                'type' => $response->headers->get('Content-Type'),
            ], $ttl);
            $response->headers->set('X-Page-Cache', 'miss');
        }

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        // Cache zapnuta pokud je aktivní v configu, nebo pokud jde o priming (X-Prime-Cache)
        $enabled = config('performance.features.full_page_cache', false) || $request->hasHeader('X-Prime-Cache');

        // Vyloučení rychle se měnících věcí, které nechceme cachovat vůbec
        // (Většina veřejných stránek se nyní cachuje a invaliduje přes PerformanceObserver)
        $excludedPaths = [
            'admin*',
            'member*',
            'clenska-sekce*',
            'telescope*',
            'horizon*',
            'up',
            'system*',
            'hledat*',
            'login',
            'logout',
            'register',
            'password*',
            'forgot-password*',
            'reset-password*',
            'two-factor*',
            'email/verify*',
            'verify-email*',
        ];

        // Pokud má request session, zkontrolujeme zda neobsahuje flash data (např. po chybě validace)
        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->has('errors') || $session->has('status') || $session->has('success') || $session->has('message')) {
                return false;
            }
        }

        return $enabled
            && $request->isMethod('GET')
            && ! $request->is($excludedPaths)
            && ! $request->hasHeader('X-Screenshot-Mode')
            && ! $request->hasHeader('X-Screenshot-Token')
            && ! $request->query('screenshot_user_id');
    }

    /**
     * Zkontroluje, zda probíhá impersonace (pokud je již session dostupná)
     */
    protected function isImpersonating(Request $request): bool
    {
        try {
            if ($request->hasSession() && $request->session()->has('impersonated_by')) {
                return true;
            }
        } catch (\Throwable $e) {
            // Session ještě nemusí být inicializována
        }

        return false;
    }
}
