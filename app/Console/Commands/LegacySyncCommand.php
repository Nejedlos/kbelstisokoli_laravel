<?php

namespace App\Console\Commands;

use Database\Seeders\AttendanceMigrationSeeder;
use Database\Seeders\EventMigrationSeeder;
use Database\Seeders\FinanceMigrationSeeder;
use Database\Seeders\MemberMigrationSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LegacySyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:legacy:sync
                            {--fresh : Před importem vymaže data v cílových tabulkách (mimo uživatelů, pokud není řečeno jinak)}
                            {--users : Povolí synchronizaci uživatelských účtů (ve výchozím stavu vypnuto)}
                            {--all : Spustí kompletní synchronizaci včetně členů a financí}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dat z původní (legacy) databáze do nového systému (ve výchozím stavu pouze zápasy a docházka).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fresh = $this->option('fresh');
        $users = $this->option('users');
        $all = $this->option('all');

        $this->info('Spouštím synchronizaci dat z legacy systému...');

        if ($all) {
            $this->warn('Režim ALL: Bude provedena kompletní synchronizace včetně členů a financí.');
        }

        if ($fresh) {
            $this->warn('Režim FRESH: Data v cílových tabulkách budou před importem smazána.');
        }

        if ($users) {
            $this->warn('Režim USERS: Synchronizace uživatelských účtů je povolena.');
        }

        // Nastavíme konfiguraci pro seedery
        config(['app.seed_users' => $users]);
        config(['app.seed_fresh' => $fresh]);

        $seeders = [
            EventMigrationSeeder::class,
            AttendanceMigrationSeeder::class,
        ];

        if ($all) {
            $seeders = [
                MemberMigrationSeeder::class,
                EventMigrationSeeder::class,
                AttendanceMigrationSeeder::class,
                FinanceMigrationSeeder::class,
            ];
        }

        foreach ($seeders as $seeder) {
            $this->info("Spouštím seeder: {$seeder}");

            // Voláme seeder přes Artisan, aby se správně předaly parametry pokud by to bylo potřeba
            // ale my je předáváme přes config výše.
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true,
            ], $this->getOutput());
        }

        $this->info('Synchronizace z legacy systému byla dokončena.');

        return self::SUCCESS;
    }
}
