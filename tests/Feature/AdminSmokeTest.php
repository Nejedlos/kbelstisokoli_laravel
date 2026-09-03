<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdmin();
        $this->with2FA($this->admin);

        // Authenticate admin for all tests in this file
        $this->actingAs($this->admin);
        $this->confirm2FA($this->admin);
    }

    /**
     * Admin otevře dashboard.
     */
    public function test_admin_can_see_dashboard(): void
    {
        $this->get('/admin')->assertStatus(200);
    }

    /**
     * Admin otevře seznam Posts.
     */
    public function test_admin_can_see_posts_index(): void
    {
        $this->get('/admin/posts')->assertStatus(200);
    }

    /**
     * Admin otevře editaci Post.
     */
    public function test_admin_can_see_post_edit_form(): void
    {
        $post = Post::create(['title' => ['cs' => 'Testovací aktualita'], 'slug' => 'test-post', 'status' => 'draft']);

        $this->get("/admin/posts/{$post->id}/edit")->assertStatus(200);
    }

    /**
     * Admin otevře lead inbox.
     */
    public function test_admin_can_see_leads_index(): void
    {
        $this->get('/admin/leads')->assertStatus(200);
    }

    /**
     * Admin otevře global settings (Branding).
     */
    public function test_admin_can_see_branding_settings(): void
    {
        $this->get('/admin/branding-settings')->assertStatus(200);
    }

    /**
     * Admin otevře audit log list.
     */
    public function test_admin_can_see_audit_logs(): void
    {
        $this->get('/admin/audit-logs')->assertStatus(200);
    }
}
