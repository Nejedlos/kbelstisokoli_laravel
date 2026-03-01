<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LegacyAttendanceSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attendance:sync
                            {--fresh : Před importem vymaže data v tabulce docházky a událostí}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Samostatná synchronizace pouze pro docházku a související události z legacy systému.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fresh = $this->option('fresh');

        $this->info('Spouštím synchronizaci docházky a událostí...');

        if ($fresh) {
            $this->warn('Režim FRESH: Data v tabulkách událostí a docházky budou smazána.');
        }

        // Nastavíme konfiguraci pro seedery
        config(['app.seed_users' => false]);
        config(['app.seed_fresh' => $fresh]);

        $seeders = [
            \Database\Seeders\EventMigrationSeeder::class,
            \Database\Seeders\AttendanceMigrationSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->info("Spouštím seeder: {$seeder}");
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true,
            ], $this->getOutput());
        }

        $this->info('Synchronizace docházky a událostí dokončena.');

        return self::SUCCESS;
    }
}
