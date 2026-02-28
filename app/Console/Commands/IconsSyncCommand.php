<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IconsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:icons:sync {--pro : Vynutit synchronizaci Pro ikon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje ikony z NPM (Font Awesome Pro) a vygeneruje cache.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- Synchronizace ikon v aplikaci ---');

        $isPro = $this->option('pro') || config('app.fontawesome_pro', false);

        if ($isPro) {
            $this->info('Synchronizuji Font Awesome Pro ikony z node_modules...');

            // Kontrola existence node_modules pro Pro ikony
            if (! is_dir(base_path('node_modules/@fortawesome/fontawesome-pro'))) {
                $this->warn('! Adresář node_modules/@fortawesome/fontawesome-pro nebyl nalezen.');
                $this->line('Na produkci je to v pořádku, pokud jste ikony synchronizovali lokálně před nahráním.');
                $this->info('Přeskakuji fyzickou synchronizaci souborů, pouze čistím a generuji cache.');
            } else {
                try {
                    $this->call('blade-fontawesome:sync-icons', ['--pro' => true]);
                    $this->info('✓ Pro ikony byly synchronizovány.');
                } catch (\Exception $e) {
                    $this->error('✗ Chyba při synchronizaci: '.$e->getMessage());
                    $this->warn('Ujistěte se, že máte nainstalován balíček @fortawesome/fontawesome-pro (npm install).');
                    // Na produkci nechceme, aby toto shodilo celý deploy, pokud už ikony máme
                    if (app()->environment('production')) {
                        $this->warn('Pokračuji dál (produkční režim)...');
                    } else {
                        return 1;
                    }
                }
            }
        } else {
            $this->warn('Režim Free: Synchronizace Pro ikon přeskočena.');
            $this->line('Pokud chcete použít Pro, nastavte FONTAWESOME_PRO=true v .env a spusťte npm install.');
        }

        $this->info('Čistím a generuji cache pro Blade Icons...');
        $this->call('icons:clear');
        $this->call('icons:cache');
        $this->info('✓ Cache ikon byla aktualizována.');

        $this->newLine();
        $this->info('✅ Vše hotovo. Ikony jsou připraveny k použití.');

        return 0;
    }
}
