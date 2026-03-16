<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeedbackSnapshotRouteTest extends TestCase
{
    /** @test */
    public function snapshot_route_returns_404_when_missing_token()
    {
        $response = $this->get('/feedback/snapshot/invalid-token');
        $response->assertStatus(404);
    }

    /** @test */
    public function snapshot_route_renders_when_token_is_present()
    {
        $token = 'abc123token';
        Cache::put('fb_snap_' . $token, [
            'dom' => '<div id="snapshot-root"><h1>Test</h1></div>',
            'context' => [],
        ], now()->addMinute());

        $response = $this->get('/feedback/snapshot/' . $token);
        $response->assertStatus(200);
        $response->assertSee('Test');
    }
}
