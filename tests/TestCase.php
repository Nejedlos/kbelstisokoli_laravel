<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected function setUp(): void
    {
        // Totální pojistka: Pokud jsme v testech a náhodou by se mělo běžet na MySQL, tak TESTY ZASTAVÍME.
        // RefreshDatabase trait v Laravelu by jinak mohl vymazat reálná data.
        // Kontrolujeme superglobální pole, abychom se vyhnuli BindingResolutionException před bootem.
        $dbConn = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? 'unknown';
        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'unknown';

        if ($dbConn === 'mysql' || $appEnv !== 'testing') {
             die("\e[31mFATAL ERROR: Testy se pokouší běžet v nevhodném prostředí! \n" .
                 "DB_CONNECTION: " . $dbConn . "\n" .
                 "APP_ENV: " . $appEnv . "\n" .
                 "Pro ochranu vaší databáze ukončuji proces. \n" .
                 "Ujistěte se, že spouštíte testy s --env=testing a máte správně nastaven phpunit.xml.\e[0m\n");
        }

        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    /**
     * Vytvoří a vrátí administrátora.
     */
    protected function createAdmin(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
        ], $attributes));

        $user->assignRole('admin');

        return $user;
    }

    /**
     * Vytvoří a vrátí běžného člena (hráče).
     */
    protected function createMember(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
        ], $attributes));

        $user->assignRole('player');

        return $user;
    }

    /**
     * Vytvoří uživatele s aktivovaným 2FA.
     */
    protected function with2FA(User $user): User
    {
        $user->forceFill([
            'two_factor_secret' => 'secret-key',
            'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->refresh();
    }
}
