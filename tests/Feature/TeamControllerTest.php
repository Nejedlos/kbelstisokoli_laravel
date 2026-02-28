<?php

namespace Tests\Feature;

use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    public function test_index_is_protected_by_manage_teams_permission(): void
    {
        $user = $this->createMember();
        // Player role doesn't have manage_teams by default

        $response = $this->actingAs($user)->get('/clenska-sekce/tymove-prehledy');

        $response->assertStatus(403);
    }

    public function test_index_is_accessible_by_admin(): void
    {
        $admin = $this->createAdmin();
        // Admin has all permissions usually

        $response = $this->actingAs($admin)->get('/clenska-sekce/tymove-prehledy');

        $response->assertStatus(200);
    }
}
