<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Výchozí administrátor
        $admin = User::updateOrCreate(
            ['email' => 'admin@basketkbely.cz'],
            [
                'name' => 'Admin Sokoli',
                'password' => Hash::make('admin123'), // Na produkci bude změněno
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Ujistíme se, že má roli admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Testovací editor (volitelně, pro vývoj)
        $editor = User::updateOrCreate(
            ['email' => 'editor@basketkbely.cz'],
            [
                'name' => 'Editor Sokoli',
                'password' => Hash::make('editor123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (!$editor->hasRole('editor')) {
            $editor->assignRole('editor');
        }

        // Michal Nejedlý – požadovaný admin účet (lokál + produkce)
        $mn = User::updateOrCreate(
            ['email' => 'nejedlymi@gmail.com'],
            [
                'first_name' => 'Michal',
                'last_name' => 'Nejedlý',
                'display_name' => 'Michal Nejedlý',
                'name' => 'Michal Nejedlý',
                'phone' => '777220966',
                'preferred_locale' => 'cs',
                'password' => Hash::make('ProcGesto?335'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        if (!$mn->hasRole('admin')) {
            $mn->assignRole('admin');
        }
    }
}
