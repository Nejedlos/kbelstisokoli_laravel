<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepagePublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed pouze CMS obsahu (bez uživatelů/hesel) kvůli rychlosti a stabilitě testů
        $this->seed(\Database\Seeders\CmsContentSeeder::class);
        $this->seed(\Database\Seeders\GdprPageSeeder::class);
    }

    public function test_homepage_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_teams_plural_route_returns_200(): void
    {
        $this->get('/tymy')->assertStatus(200);
    }

    public function test_singular_teams_route_redirects_to_plural(): void
    {
        $this->get('/tym')->assertRedirect('/tymy');
        $this->get('/tym')->assertStatus(301);
    }

    public function test_internal_recruitment_page_exists_or_redirects(): void
    {
        // V našem seedingu existuje CMS stránka /nabor, takže očekáváme 200
        $this->get('/nabor')->assertStatus(200);
    }

    public function test_footer_privacy_page_exists(): void
    {
        $this->get('/gdpr')->assertStatus(200);
    }
}
