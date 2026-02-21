<?php

namespace Tests\Feature;

use App\Models\User;
\App\Models\User::class;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_profile_page_requires_permission_and_returns_403_without_it()
    {
        $user = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/clenska-sekce/profil');

        // Middleware is resolved and applied; without permission it should be forbidden
        $response->assertStatus(403);
    }
}
