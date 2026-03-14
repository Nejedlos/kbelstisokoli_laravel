<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ImpersonationAccessTest extends TestCase
{
    /**
     * Testuje, že impersonovaný uživatel bez admin rolí NEMÁ přístup do administrace.
     */
    public function test_impersonated_player_cannot_access_admin(): void
    {
        $admin = $this->createAdmin();
        $player = $this->createMember(); // Role 'player'

        // Simulujeme impersonaci hráče adminem
        $this->actingAs($player);
        session(['impersonated_by' => $admin->id]);

        // Hráč by neměl mít přístup do adminu ani při impersonaci
        $response = $this->get('/admin');

        $response->assertStatus(403);
    }

    /**
     * Testuje, že impersonovaný uživatel s admin rolí MÁ přístup do administrace.
     */
    public function test_impersonated_coach_can_access_admin(): void
    {
        $admin = $this->createAdmin();
        $coach = User::factory()->create(['is_active' => true]);
        $coach->assignRole('coach');

        // Simulujeme impersonaci coache adminem
        $this->actingAs($coach);
        session(['impersonated_by' => $admin->id]);

        // Coach má standardně přístup do adminu (v canAccessAdmin)
        // Musíme ale také simulovat 2FA potvrzení, protože 2FA je pro admin cesty vyžadováno ( EnsureTwoFactorEnabled )
        // nicméně EnsureTwoFactorEnabled má výjimku pro impersonaci, takže by to mělo projít i bez 2FA session klíče.

        $response = $this->get('/admin');

        // Pokud projde přes canAccessAdmin (true) a EnsureTwoFactorEnabled (true díky impersonaci),
        // dostane se na dashboard.
        $response->assertStatus(200);
    }
}
