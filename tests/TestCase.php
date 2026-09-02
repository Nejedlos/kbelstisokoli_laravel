<?php

namespace Tests;

use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();

        // Check resolved configuration before RefreshDatabase can run migrations,
        // including when a developer accidentally leaves a configuration cache.
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Tests require the testing environment and SQLite :memory:.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        // Totální pojistka: Pokud jsme v testech a náhodou by se mělo běžet na MySQL, tak TESTY ZASTAVÍME.
        // RefreshDatabase trait v Laravelu by jinak mohl vymazat reálná data.
        // Kontrolujeme superglobální pole, abychom se vyhnuli BindingResolutionException před bootem.
        $dbConn = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? 'unknown';
        $appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'unknown';

        if ($dbConn === 'mysql' || $appEnv !== 'testing') {
            exit("\e[31mFATAL ERROR: Testy se pokouší běžet v nevhodném prostředí! \n".
                'DB_CONNECTION: '.$dbConn."\n".
                'APP_ENV: '.$appEnv."\n".
                "Pro ochranu vaší databáze ukončuji proces. \n".
                "Ujistěte se, že spouštíte testy s --env=testing a máte správně nastaven phpunit.xml.\e[0m\n");
        }

        parent::setUp();

        $this->seed(PermissionSeeder::class);
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
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->refresh();
    }

    protected function confirm2FA(User $user): void
    {
        session([
            'auth.2fa_confirmed_at' => now()->timestamp,
            'auth.2fa_fingerprint' => app(TwoFactorService::class)->fingerprint($user),
        ]);
    }
}
