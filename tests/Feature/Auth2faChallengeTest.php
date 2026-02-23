<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Auth2faChallengeTest extends TestCase
{
    /**
     * Testuje, že přihlášený admin s vypršelým 2FA timeoutem je přesměrován na challenge
     * a že se mu tato challenge korektně zobrazí (nespadne do redirect loopu).
     */
    public function test_logged_in_admin_needs_2fa_reconfirmation(): void
    {
        $admin = $this->createAdmin([
            'password' => Hash::make('password'),
        ]);
        $this->with2FA($admin);

        // 1. Fáze: Přihlášení (přes náš LoginResponse)
        // Simulujeme to, co se děje po úspěšném zadání hesla
        $this->actingAs($admin);

        // Pokud jdeme na /admin, CheckTwoFactorTimeout by nás měl hodit na challenge
        // protože nemáme 'auth.2fa_confirmed_at' v session.
        $response = $this->get('/admin');

        $response->assertRedirect(route('two-factor.login'));

        // 2. Fáze: Zobrazení challenge
        // Zde je kámen úrazu - pokud má route 'two-factor.login' middleware 'guest',
        // tak nás to vykopne zpět na 'home' (/clenska-sekce/dashboard).
        $response2 = $this->followRedirects($response);

        // Pokud je tam redirect loop nebo špatný redirect, toto selže.
        // Očekáváme, že uvidíme challenge view (obsahuje např. text "6místný")
        $response2->assertStatus(200);
        $response2->assertSee('6místný');
    }
}
