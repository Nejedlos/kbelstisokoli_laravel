<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClubIdentifierGenerationTest extends TestCase
{
    /**
     * Testuje, že při přihlášení uživatele bez club_member_id a payment_vs se tyto údaje vygenerují.
     */
    public function test_club_identifiers_are_generated_on_login(): void
    {
        // 1. Vytvoříme uživatele bez ID
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'club_member_id' => null,
            'payment_vs' => null,
            'is_active' => true,
        ]);
        $user->assignRole('player');

        $this->assertNull($user->club_member_id);
        $this->assertNull($user->payment_vs);

        // 2. Provedeme login
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();

        // 3. Ověříme, že uživatel má nyní vygenerovaná ID v databázi
        $user->refresh();

        $this->assertNotNull($user->club_member_id, 'Club member ID should be generated');
        $this->assertNotNull($user->payment_vs, 'Payment VS should be generated');

        // Ověříme formáty (podle ClubIdentifierService)
        // KS-RRXXXX
        $this->assertMatchesRegularExpression('/^KS-\d{6}$/', $user->club_member_id);
        // RRMMXXXX
        $this->assertMatchesRegularExpression('/^\d{8}$/', $user->payment_vs);

        // Ověříme, že nejsou "nehezká" (1 nebo 000001)
        $this->assertNotEquals('1', $user->payment_vs);
        $this->assertNotEquals('000001', $user->payment_vs);
    }

    /**
     * Testuje, že pokud uživatel ID již má, zůstanou zachována.
     */
    public function test_existing_club_identifiers_are_preserved_on_login(): void
    {
        $existingId = 'KS-991234';
        $existingVs = '99011234';

        $user = User::factory()->create([
            'email' => 'preserved@example.com',
            'password' => Hash::make('password'),
            'club_member_id' => $existingId,
            'payment_vs' => $existingVs,
            'is_active' => true,
        ]);
        $user->assignRole('player');

        $this->post(route('login'), [
            'email' => 'preserved@example.com',
            'password' => 'password',
        ]);

        $user->refresh();

        $this->assertEquals($existingId, $user->club_member_id);
        $this->assertEquals($existingVs, $user->payment_vs);
    }
}
