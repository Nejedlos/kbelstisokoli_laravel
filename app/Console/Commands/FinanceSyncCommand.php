<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FinanceSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:sync {--fresh : Smaže finanční data před synchronizací} {--import : Spustí import dat ze staré DB} {--force : Vynutí akci bez potvrzení}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje finanční data, stavy předpisů a volitelně provádí import ze staré DB.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->warn('⚠️  Pozor: Příznak --fresh SMAŽE všechna finanční data!');

            $confirmed = $this->option('force') || $this->confirm('Opravdu chcete smazat finanční data před synchronizací?', false);

            if ($confirmed) {
                $this->call('finance:cleanup', ['--force' => true]);
            }
        }

        if ($this->option('import') || $this->option('fresh')) {
            $this->info('Spouštím import finančních dat ze staré DB...');
            $this->call('db:seed', ['--class' => 'FinanceMigrationSeeder', '--force' => true]);
        }

        $this->info('Spouštím synchronizaci statusů financí...');
        \App\Jobs\FinanceSyncJob::dispatchSync();
        $this->info('Hotovo.');
    }
}
