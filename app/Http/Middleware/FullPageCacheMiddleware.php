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
        $cacheKey = 'full_page_'.md5($request->path().'_'.serialize($queryParams).'_'.app()->getLocale());
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

        if ($response->getStatusCode() === 200 && ! str_contains($response->getContent(), 'wire:initial-data')) {
            // Neukládáme Livewire komponenty (mohlo by to rozbít session/tokens),
            // ledaže bychom to ošetřili lépe. Pro začátek jen čisté statické stránky.
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'type' => $response->headers->get('Content-Type'),
            ], $ttl);
            $response->headers->set('X-Page-Cache', 'miss');
        }

        return $response;
    }

    protected function shouldCache(Request $request): bool
    {
        // Povolíme i query parametry (hashovány výše), ale vynecháme admin/member
        return config('performance.features.full_page_cache', false)
            && $request->isMethod('GET')
            && ! auth()->check() // Pouze pro hosty (zatím)
            && ! $request->is('admin*')
            && ! $request->is('member*');
    }
}
