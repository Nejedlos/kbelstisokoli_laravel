<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        \Filament\Support\Facades\FilamentIcon::register([
            'fal_basketball' => 'heroicon-o-cake',
            'fal_basketball_hoop' => 'heroicon-o-cake',
        ]);
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
