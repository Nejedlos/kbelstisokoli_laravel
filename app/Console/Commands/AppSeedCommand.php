<?php

namespace App\Console\Commands;

use App\Models\Page;
use Database\Seeders\CmsContentSeeder;
use Database\Seeders\GdprPageSeeder;
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
                            {--users : Povolí seedování uživatelů (UserSeeder, MemberMigrationSeeder)}
                            {--frontend-only : Spustí pouze seedery frontendového obsahu (CmsContentSeeder, GdprPageSeeder)}
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
        $users = (bool) $this->option('users');
        $frontendOnly = (bool) $this->option('frontend-only');
        $class = $this->option('class');

        // Seedování uživatelů povolíme buď explicitně příznakem, nebo automaticky při fresh seedu
        $seedUsers = $users || $fresh;
        config(['app.seed_users' => $seedUsers]);

        if ($seedUsers) {
            $this->line('🛡️  Seedování uživatelů: <info>POVOLENO</info>');
        } else {
            $this->line('🛡️  Seedování uživatelů: <comment>PŘESKOČENO</comment> (použijte --users pro vynucení)');
        }

        // Informativní výpis aktivní DB
        try {
            $default = config('database.default');
            $conn = (array) config("database.connections.$default");
            $dbInfo = sprintf('%s://%s:%s/%s', $default, $conn['host'] ?? '-', $conn['port'] ?? '-', $conn['database'] ?? '-');
            $this->line("🔌 Použitá databáze: <comment>{$dbInfo}</comment>");
        } catch (\Throwable $e) {
            // ignore
        }

        // Počty stránek před seedem (diagnostika)
        $pagesBefore = Page::query()->count();
        $this->line("📄 Pages (před): <comment>{$pagesBefore}</comment>");

        // Normalizace názvu třídy, pokud uživatel zadá jen název
        if (! str_contains($class, '\\')) {
            $class = "Database\\Seeders\\{$class}";
        }

        if ($fresh) {
            $this->warn('!!! VAROVÁNÍ !!!');
            $this->warn('Fresh režim smaže stávající produkční data v dotčených tabulkách.');

            // Na produkci vyžadujeme potvrzení nebo --no-interaction
            if (app()->environment('production') && ! $this->option('no-interaction')) {
                if (! $this->confirm('Opravdu chcete smazat data na PRODUKCI?', false)) {
                    $this->info('Akce zrušena.');

                    return self::SUCCESS;
                }
            } elseif (! $this->option('no-interaction')) {
                if (! $this->confirm('Opravdu chcete smazat stávající data v databázi?', false)) {
                    $this->info('Akce zrušena.');

                    return self::SUCCESS;
                }
            }

            $this->wipeData();
        }

        $result = 0;

        if ($frontendOnly) {
            $this->info('Spouštím seedování: CmsContentSeeder + GdprPageSeeder (frontend-only)');
            $seeders = [CmsContentSeeder::class, GdprPageSeeder::class];
            foreach ($seeders as $seederClass) {
                $params = ['--class' => $seederClass];
                if ($this->option('force')) {
                    $params['--force'] = true;
                }
                if ($this->option('no-interaction')) {
                    $params['--no-interaction'] = true;
                }
                $r = Artisan::call('db:seed', $params);
                $this->line(Artisan::output());
                if ($r !== 0) {
                    $result = $r;
                    break;
                }
            }
        } else {
            $this->info("Spouštím seedování: {$class}");
            $params = ['--class' => $class];
            if ($this->option('force')) {
                $params['--force'] = true;
            }
            if ($this->option('no-interaction')) {
                $params['--no-interaction'] = true;
            }
            $result = Artisan::call('db:seed', $params);
        }

        if ($result === 0) {
            $this->info('Seedování proběhlo úspěšně.');

            // Počty stránek po seedu a audit vybraných slugů
            $pagesAfter = Page::query()->count();
            $this->line("📄 Pages (po): <comment>{$pagesAfter}</comment> (Δ ".($pagesAfter - $pagesBefore).')');
            $slugs = ['home', 'o-klubu', 'nabor', 'treninky', 'zapasy', 'tymy', 'kontakt', 'gdpr'];
            $found = Page::query()->whereIn('slug', $slugs)->pluck('slug')->all();
            $this->line('🔎 Frontend slugs přítomné: <comment>'.implode(', ', $found).'</comment>');

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

                // Resetování auto-incrementu
                try {
                    $prefix = DB::getTablePrefix();
                    if (config('database.default') === 'mysql') {
                        DB::statement("ALTER TABLE `{$prefix}{$table}` AUTO_INCREMENT = 1");
                    } elseif (config('database.default') === 'sqlite') {
                        DB::statement("DELETE FROM sqlite_sequence WHERE name='{$prefix}{$table}'");
                    }
                } catch (\Throwable $e) {
                    $this->warn("  - Nepodařilo se resetovat auto-increment pro: {$table}");
                }
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->info('Čištění dokončeno.');
    }
}
