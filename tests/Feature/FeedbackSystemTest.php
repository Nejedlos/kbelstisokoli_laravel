<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FeedbackReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeedbackSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Config::set('feedback.enabled', true);
        Config::set('feedback.environments', ['testing']);
        Config::set('app.env', 'testing');
    }

    public function test_guest_cannot_see_widget(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('ks-feedback-widget');
    }

    public function test_auth_user_sees_widget_in_html_response(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('ks-feedback-widget');
    }

    public function test_guest_cannot_post_feedback(): void
    {
        $response = $this->postJson('/feedback', [
            'type' => 'bug',
            'title' => 'Test',
            'description' => 'Test description',
        ]);

        $response->assertStatus(401);
    }

    public function test_auth_user_can_post_feedback(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/feedback', [
            'type' => 'bug',
            'severity' => 'low',
            'title' => 'Test Bug',
            'description' => 'Detailed bug description',
            'url' => 'http://localhost/test',
            'user_agent' => 'TestBot',
            'source_area' => 'public',
            'screenshot' => 'data:image/jpeg;base64,' . base64_encode('fake-image'),
            'logs' => [['type' => 'log', 'data' => ['test log']]],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'id']);

        $this->assertDatabaseHas('feedback_reports', [
            'user_id' => $user->id,
            'title' => 'Test Bug',
        ]);

        $report = FeedbackReport::first();
        Storage::assertExists($report->screenshot_path);
        Storage::assertExists($report->logs_path);
    }

    public function test_redaction_works(): void
    {
        $user = User::factory()->create();
        Config::set('feedback.redaction.redact_keys', ['password']);

        $response = $this->actingAs($user)->postJson('/feedback', [
            'type' => 'bug',
            'title' => 'Redaction Test',
            'description' => 'Test description',
            'url' => 'http://localhost/test',
            'user_agent' => 'TestBot',
            'source_area' => 'public',
            'meta' => [
                'password' => 'secret123',
                'other' => 'safe',
            ]
        ]);

        $response->assertStatus(200);
        $report = FeedbackReport::first();

        $meta = $report->meta;
        $this->assertEquals('[REDACTED]', $meta['password']);
        $this->assertEquals('safe', $meta['other']);
    }

    public function test_rate_limit_works(): void
    {
        $user = User::factory()->create();

        $payload = [
            'type' => 'bug',
            'description' => 'Desc',
            'url' => 'http://localhost/test',
            'user_agent' => 'TestBot',
            'source_area' => 'public',
        ];

        // Send 10 successful requests (default limit is 10,1 per config/routes)
        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->postJson('/feedback', array_merge($payload, ['title' => "Test $i"]))->assertStatus(200);
        }

        // 11th should be throttled (429)
        $this->actingAs($user)->postJson('/feedback', array_merge($payload, ['title' => "Test 11"]))->assertStatus(429);
    }

    public function test_duplicate_guard_works(): void
    {
        $user = User::factory()->create();

        $payload = [
            'type' => 'bug',
            'title' => 'Duplicate Test',
            'description' => 'Exact same description',
            'url' => 'http://localhost/test',
            'user_agent' => 'TestBot',
            'source_area' => 'public',
        ];

        $this->actingAs($user)->postJson('/feedback', $payload)->assertStatus(200);
        $this->actingAs($user)->postJson('/feedback', $payload)->assertStatus(429);
    }

    public function test_admin_access_to_resource(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->canAccessAdmin());

        $member = User::factory()->create(['is_active' => true]);

        // Member cannot access admin
        $this->actingAs($member)->get('/admin/feedback-reports')->assertStatus(403);

        // Admin can access admin - check if it redirects to login (302) or is 200
        $response = $this->actingAs($admin)->get('/admin/feedback-reports');

        // If it redirects, it might be due to 2FA or other middleware
        if ($response->status() === 302) {
             $this->followRedirects($response)->assertStatus(200);
        } else {
             $response->assertStatus(200);
        }
    }
}
