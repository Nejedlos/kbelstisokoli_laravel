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
        $shouldCache = $this->shouldCache($request);
        if (! $isGuest || ! $shouldCache || $this->isImpersonating($request)) {
            $response = $next($request);
            $response->headers->set('X-Page-Cache-Skip', ! $isGuest ? 'not-guest' : (! $shouldCache ? $request->attributes->get('cache_skip_reason', 'unknown') : 'impersonating'));

            return $response;
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

            // Browser cache na 5 minut pro veřejné stránky (minimalizace TTFB při navigaci)
            $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=600');

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

        if (! $enabled) {
            $request->attributes->set('cache_skip_reason', 'config-disabled');
            return false;
        }

        if (! $request->isMethod('GET')) {
            $request->attributes->set('cache_skip_reason', 'not-get');
            return false;
        }

        // Vyloučení rychle se měnících věcí, které nechceme cachovat vůbec
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

        if ($request->is($excludedPaths)) {
            $request->attributes->set('cache_skip_reason', 'excluded-path');
            return false;
        }

        // Pokud má request session, zkontrolujeme zda neobsahuje flash data
        if ($request->hasSession()) {
            $session = $request->session();
            if ($session->has('errors') || $session->has('status') || $session->has('success') || $session->has('message')) {
                $request->attributes->set('cache_skip_reason', 'session-flash-data');
                return false;
            }
        }

        return true;
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
