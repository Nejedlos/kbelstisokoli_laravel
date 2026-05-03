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
        // Rychlá kontrola pro hosty (před StartSession)
        // Pokud middleware běží dříve než StartSession, auth()->check() nebude fungovat.
        // V takovém případě se spoléháme na absenci session cookie.
        $isGuest = true;
        try {
            if (auth()->check()) {
                $isGuest = false;
            }
        } catch (\Throwable $e) {
            // Auth zatím není připraven, zkusíme detekci přes cookie
            $sessionCookie = config('session.cookie');
            if ($request->hasCookie($sessionCookie)) {
                $isGuest = false; // Má cookie, může být přihlášen -> neriskujeme cache
            }
        }

        // Cache zapnuta pouze pro GET, hosty a pokud je aktivní v configu
        if (! $isGuest || ! $this->shouldCache($request)) {
            return $next($request);
        }

        // Zahrneme do klíče i query parametry (seřazené) a jazyk
        $queryParams = $request->query();
        ksort($queryParams);
        $locale = app()->getLocale();
        $cacheKey = 'full_page_'.md5($request->path().'_'.serialize($queryParams).'_'.$locale);
        $ttl = config('performance.cache_ttl.full_page', 86400);

        if (Cache::has($cacheKey)) {
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
        if ($response->getStatusCode() === 200
            && ! empty($content)
            && ! $request->hasHeader('X-Livewire')
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

        // Vyloučení rychle se měnících věcí
        $excludedPaths = [
            'novinky*',
            'zapasy*',
            'treninky*',
            'akce*',
            'admin*',
            'member*',
            'telescope*',
            'horizon*',
        ];

        return $enabled
            && $request->isMethod('GET')
            && ! $request->is($excludedPaths);
    }
}
