<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeasonMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldDb = config('database.old_database');
        if (!$oldDb) {
            $this->command->error('Databáze pro migraci nebyla nalezena (DB_DATABASE_OLD ani DB_DATABASE).');
            return;
        }

        $this->command->info('Zjišťuji sezóny ze staré DB...');

        // Získáme unikátní sezóny ze všech tabulek, kde se vyskytují
        try {
            $seasons = collect();

            $tables = ['zapasy', 'dochazka', 'web_realna_dochazka', 'web_platici'];
            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($oldDb . '.' . $table)) {
                    $names = \Illuminate\Support\Facades\DB::table($oldDb . '.' . $table)
                        ->select('sezona')
                        ->distinct()
                        ->pluck('sezona')
                        ->filter();
                    $seasons = $seasons->merge($names);
                }
            }

            $uniqueSeasons = $seasons->unique()->sort()->values();

            if ($uniqueSeasons->isEmpty()) {
                $this->command->warn('Nebyly nalezeny žádné sezóny k migraci.');
                return;
            }

            $this->command->info('Migrace ' . $uniqueSeasons->count() . ' sezón...');

            foreach ($uniqueSeasons as $name) {
                $unifiedName = str_replace('-', '/', $name);
                \App\Models\Season::updateOrCreate(
                    ['name' => $unifiedName],
                    ['is_active' => false]
                );
            }

            // Nastavíme nejnovější sezónu jako aktivní
            $latest = \App\Models\Season::orderBy('name', 'desc')->first();
            if ($latest) {
                $latest->update(['is_active' => true]);
                $this->command->info("Sezóna {$latest->name} byla nastavena jako aktivní.");
            }

        } catch (\Exception $e) {
            $this->command->error('Chyba při migraci sezón: ' . $e->getMessage());
        }
    }
}
