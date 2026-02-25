<?php

namespace App\Console\Commands;

use Database\Seeders\GlobalSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed
                            {--fresh : Smaže všechna data v dotčených tabulkách před seedováním}
                            {--force : Vynutí spuštění na produkci}
                            {--class=Database\\Seeders\\GlobalSeeder : Třída seederu, který se má spustit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí globální nebo specifické seedování s podporou fresh režimu a idempotence.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fresh = $this->option('fresh');
        $class = $this->option('class');

        // Normalizace názvu třídy, pokud uživatel zadá jen název
        if (!str_contains($class, '\\')) {
            $class = "Database\\Seeders\\{$class}";
        }

        if ($fresh) {
            $this->warn('!!! VAROVÁNÍ !!!');
            $this->warn('Fresh režim smaže stávající produkční data v dotčených tabulkách.');

            // Na produkci vyžadujeme potvrzení nebo --no-interaction
            if (app()->environment('production') && !$this->option('no-interaction')) {
                if (!$this->confirm('Opravdu chcete smazat data na PRODUKCI?', false)) {
                    $this->info('Akce zrušena.');
                    return self::SUCCESS;
                }
            } elseif (!$this->option('no-interaction')) {
                if (!$this->confirm('Opravdu chcete smazat stávající data v databázi?', false)) {
                    $this->info('Akce zrušena.');
                    return self::SUCCESS;
                }
            }

            $this->wipeData();
        }

        $this->info("Spouštím seedování: {$class}");

        $params = [
            '--class' => $class,
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        // Propagujeme no-interaction
        if ($this->option('no-interaction')) {
            $params['--no-interaction'] = true;
        }

        $result = Artisan::call('db:seed', $params);

        if ($result === 0) {
            $this->info('Seedování proběhlo úspěšně.');
            $this->line(Artisan::output());

            // Vyčistíme cache, aby se změny projevily hned
            $this->info('Čistím cache...');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            if ($fresh) {
                Artisan::call('config:clear');
                Artisan::call('route:clear');
            }
            $this->info('Cache vyčištěna.');
        } else {
            $this->error('Během seedování došlo k chybě.');
            $this->line(Artisan::output());
        }

        return $result;
    }

    /**
     * Smaže data z tabulek definovaných pro fresh seed.
     */
    protected function wipeData(): void
    {
        $this->info('Čistím tabulky definované v GlobalSeeder...');

        Schema::disableForeignKeyConstraints();

        foreach (GlobalSeeder::TABLES_TO_WIPE as $table) {
            if (Schema::hasTable($table)) {
                $this->line("- Čištění tabulky: <comment>{$table}</comment>");

                // Používáme DB::table()->delete() pro maximální kompatibilitu napříč DB drivery
                // (zejména SQLite na hostingu může mít s TRUNCATE problémy u cizích klíčů)
                DB::table($table)->delete();
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->info('Čištění dokončeno.');
    }
}
