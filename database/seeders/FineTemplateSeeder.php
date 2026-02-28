<?php

namespace Database\Seeders;

use App\Models\FineTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FineTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldDb = config('database.old_database');
        if (! $oldDb) {
            $this->command->error('Databáze pro migraci nebyla nalezena (DB_DATABASE_OLD ani DB_DATABASE).');

            return;
        }

        $this->command->info('Migruji šablony pokut ze staré DB...');

        try {
            $oldFineTypes = DB::connection('old_mysql')->table($oldDb.'.web_vypocty_pokuty')->get();

            foreach ($oldFineTypes as $oft) {
                FineTemplate::updateOrCreate(
                    ['metadata->legacy_id' => $oft->id],
                    [
                        'name' => ['cs' => $oft->nazev],
                        'default_amount' => $oft->pausal,
                        'unit' => $oft->jednotka,
                        'metadata' => ['legacy_id' => $oft->id],
                    ]
                );
            }

            $this->command->info('Migrace šablon pokut dokončena.');

        } catch (\Exception $e) {
            $this->command->error('Chyba při migraci šablon pokut: '.$e->getMessage());
        }
    }
}
