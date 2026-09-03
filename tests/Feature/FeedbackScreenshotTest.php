<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ScreenshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Tests\TestCase;

class FeedbackScreenshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('feedback.enabled', true);
        Config::set('feedback.screenshot.playwright.enabled', true);
    }

    public function test_server_screenshot_endpoint_requires_auth(): void
    {
        $response = $this->postJson('/feedback/screenshot', [
            'dom' => '<html><body>Test</body></html>',
        ]);

        $response->assertStatus(401);
    }

    public function test_server_screenshot_endpoint_works_with_mocked_service(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->mock(ScreenshotService::class, function (MockInterface $mock) {
            $mock->shouldReceive('captureViaPlaywrightFromDom')
                ->once()
                ->with('<html><body>Test</body></html>', \Mockery::any())
                ->andReturn([
                    'data_url' => 'data:image/png;base64,fake',
                    'width' => 1920,
                    'height' => 1080,
                    'mime' => 'image/png',
                    'path' => '/tmp/test.png',
                ]);
        });

        $response = $this->actingAs($user)->postJson('/feedback/screenshot', [
            'dom' => '<html><body>Test</body></html>',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'image' => 'data:image/png;base64,fake',
            'width' => 1920,
            'height' => 1080,
        ]);
    }

    public function test_snapshot_route_requires_valid_token(): void
    {
        $response = $this->get('/feedback/snapshot/invalid-token');
        $response->assertStatus(404);
    }

    public function test_snapshot_route_works_with_valid_token(): void
    {
        $token = 'test-token-123';
        Cache::put("fb_snap_{$token}", [
            'dom' => '<h1>Hello World</h1>',
            'context' => [
                'body_class' => 'test-body',
                'html_class' => 'test-html',
            ],
        ], 60);

        $response = $this->get("/feedback/snapshot/{$token}");
        $response->assertStatus(200);
        $response->assertSee('Hello World');
        $response->assertSee('test-body');
        $response->assertSee('test-html');
    }

    public function test_report_submission_works_without_screenshot_on_failure(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        // Simulate report submission where capture.screenshot is null (simulating failure on frontend)
        $response = $this->actingAs($user)->postJson('/feedback', [
            'type' => 'bug',
            'title' => 'Test without screenshot',
            'description' => 'Description',
            'context' => [
                'url' => 'http://localhost/test',
                'area' => 'public',
            ],
            'capture' => [
                'screenshot' => null, // Failed screenshot
                'screenshot_meta' => [
                    'strategy' => 'server',
                    'error' => 'Playwright failed',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('feedback_reports', [
            'title' => 'Test without screenshot',
        ]);
    }
}
