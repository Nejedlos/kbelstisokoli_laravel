<?php

namespace Tests\Feature;

use App\Models\Page;
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
        session(['auth.2fa_confirmed_at' => now()->timestamp]);
    }

    /**
     * Admin otevře dashboard.
     */
    public function test_admin_can_see_dashboard(): void
    {
        $this->get('/admin')->assertStatus(200);
    }

    /**
     * Admin otevře seznam Pages.
     */
    public function test_admin_can_see_pages_index(): void
    {
        $this->get('/admin/pages')->assertStatus(200);
    }

    /**
     * Admin otevře editaci Page.
     */
    public function test_admin_can_see_page_edit_form(): void
    {
        $page = Page::factory()->create();

        $this->get("/admin/pages/{$page->id}/edit")->assertStatus(200);
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
