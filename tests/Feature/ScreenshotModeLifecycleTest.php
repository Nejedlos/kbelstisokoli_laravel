<?php

namespace Tests\Feature;

use App\Http\Middleware\DetectScreenshotMode;
use App\Support\ScreenshotMode;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ScreenshotModeLifecycleTest extends TestCase
{
    public function test_screenshot_mode_is_limited_to_the_current_request(): void
    {
        $middleware = app(DetectScreenshotMode::class);
        $middleware->handle(Request::create('/?screenshot=1'), function () {
            $this->assertTrue(ScreenshotMode::isActive());

            return new Response('screenshot');
        });
        $this->assertFalse(ScreenshotMode::isActive());
        $this->assertNull(ScreenshotMode::getUserId());

        $middleware->handle(Request::create('/'), function () {
            $this->assertFalse(ScreenshotMode::isActive());

            return new Response('ordinary request');
        });
    }

    public function test_exception_also_clears_screenshot_state(): void
    {
        try {
            app(DetectScreenshotMode::class)->handle(Request::create('/?screenshot=1'), function () {
                $this->assertTrue(ScreenshotMode::isActive());
                ScreenshotMode::activate(7);
                throw new RuntimeException('Synthetic request failure');
            });
            $this->fail('Expected the request exception to propagate.');
        } catch (RuntimeException $error) {
            $this->assertSame('Synthetic request failure', $error->getMessage());
        }
        $this->assertFalse(ScreenshotMode::isActive());
        $this->assertNull(ScreenshotMode::getUserId());
    }
}
