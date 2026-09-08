<?php

namespace Tests\Feature;

use App\Http\Middleware\FullPageCacheMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FullPageCacheReleaseIsolationTest extends TestCase
{
    public function test_cached_html_is_isolated_between_releases(): void
    {
        Cache::flush();
        config(['performance.features.full_page_cache' => true]);
        $middleware = app(FullPageCacheMiddleware::class);

        config(['app.release_sha' => str_repeat('a', 40)]);
        $first = $middleware->handle(
            Request::create('/release-cache-test', 'GET'),
            fn () => response('release-a'),
        );

        config(['app.release_sha' => str_repeat('b', 40)]);
        $second = $middleware->handle(
            Request::create('/release-cache-test', 'GET'),
            fn () => response('release-b'),
        );

        config(['app.release_sha' => str_repeat('a', 40)]);
        $firstAgain = $middleware->handle(
            Request::create('/release-cache-test', 'GET'),
            fn () => response('unexpected'),
        );

        $this->assertSame('release-a', $first->getContent());
        $this->assertSame('release-b', $second->getContent());
        $this->assertSame('release-a', $firstAgain->getContent());
        $this->assertSame('hit', $firstAgain->headers->get('X-Page-Cache'));
    }
}
