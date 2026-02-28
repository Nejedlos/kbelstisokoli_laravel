<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tento seeder slouží k opakovanému spuštění migrace dat ze starých tabulek do nového systému.
 * Je bezpečné jej pouštět opakovaně na produkci, protože vnitřní seedery by měly používat
 * metodu updateOrCreate nebo podobné logiky pro zamezení duplicit.
 */
class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldDb = config('database.old_database');
        $this->command->info('Používám databázi pro migraci: '.($oldDb ?: 'NENASTAVENO'));

        $this->call([
            LegacyUserMigrationSeeder::class,     // Migrace uživatelů a profilů
            SeasonMigrationSeeder::class,         // Migrace sezón a konfigurací
            EventMigrationSeeder::class,          // Migrace zápasů a tréninků
            AttendanceMigrationSeeder::class,     // Migrace docházky
            FinanceMigrationSeeder::class,        // Migrace plateb a předpisů
        ]);
    }
}
