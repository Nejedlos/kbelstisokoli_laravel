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
        // Cache zapnuta pouze pro GET, a pokud je aktivní v configu
        if (! $this->shouldCache($request)) {
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
        // 3. Nejde o Livewire request (X-Livewire header) - ten se cachuje samostatně přes fragmenty pokud vůbec
        if ($response->getStatusCode() === 200
            && ! empty($content)
            && ! $request->hasHeader('X-Livewire')
        ) {
            // POZN: Povolujeme i Livewire 3 snapshots pro hosty.
            // Jelikož jsme v by-passu pro auth() uživatele, snapshoty by měly být
            // pro všechny hosty identické (pokud nepoužívají session-specific data).
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

        // Vyloučení rychle se měnících věcí (zápasy, tréninky, akce, novinky)
        $excludedPaths = [
            'novinky*',
            'zapasy*',
            'treninky*',
            'akce*',
            'admin*',
            'member*',
        ];

        return $enabled
            && $request->isMethod('GET')
            && ! auth()->check() // Pouze pro hosty (zatím)
            && ! $request->is($excludedPaths);
    }
}
