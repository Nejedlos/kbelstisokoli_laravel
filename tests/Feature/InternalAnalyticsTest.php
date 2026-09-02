<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class InternalAnalyticsTest extends TestCase
{
    public function test_it_tracks_frontend_requests()
    {
        $this->get('/');

        $this->assertDatabaseHas('internal_analytics_events', [
            'path' => '/',
            'area' => 'frontend',
        ]);
    }

    public function test_it_tracks_authenticated_user()
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);

        $this->assertDatabaseHas('internal_analytics_events', [
            'user_id' => $user->id,
            'is_authenticated' => true,
            'event_type' => 'page_view',
        ]);
    }

    public function test_it_ignores_assets()
    {
        $this->get('/favicon.ico');

        $this->assertDatabaseMissing('internal_analytics_events', [
            'path' => 'favicon.ico',
        ]);
    }
}
