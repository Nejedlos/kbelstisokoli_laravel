<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Tests\TestCase;

class RedirectRulesTest extends TestCase
{
    /**
     * RedirectMiddleware se uplatní na veřejné cestě.
     */
    public function test_public_redirect_applies(): void
    {
        // Arrange: záznam pro /novinky -> /
        Redirect::create([
            'source_path' => '/novinky',
            'target_type' => 'internal',
            'target_path' => '/',
            'status_code' => 302,
            'is_active' => true,
            'match_type' => 'exact',
            'priority' => 10,
        ]);

        // Act & Assert
        $this->get('/novinky')->assertRedirect('/');
    }

    /**
     * RedirectMiddleware se NEuplatní na admin cestě.
     */
    public function test_admin_redirect_rule_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $this->with2FA($admin);
        $this->actingAs($admin);
        session(['auth.2fa_confirmed_at' => now()->timestamp]);

        // Arrange: záznam, který by jinak posílal /admin na /
        Redirect::create([
            'source_path' => '/admin',
            'target_type' => 'internal',
            'target_path' => '/',
            'status_code' => 302,
            'is_active' => true,
            'match_type' => 'exact',
            'priority' => 100,
        ]);

        // Act
        $response = $this->get('/admin');

        // Assert: žádný redirect na /, admin dashboard je dostupný
        $this->assertNotTrue($response->isRedirect('/'));
        $response->assertStatus(200);
    }
}
