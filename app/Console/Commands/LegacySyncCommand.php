<?php

namespace App\Console\Commands;

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
                            {--users : Povolí synchronizaci uživatelských účtů (ve výchozím stavu vypnuto)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dat z původní (legacy) databáze do nového systému.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fresh = $this->option('fresh');
        $users = $this->option('users');

        $this->info('Spouštím synchronizaci dat z legacy systému...');

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
            \Database\Seeders\MemberMigrationSeeder::class,
            \Database\Seeders\EventMigrationSeeder::class,
            \Database\Seeders\AttendanceMigrationSeeder::class,
            \Database\Seeders\FinanceMigrationSeeder::class,
        ];

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
