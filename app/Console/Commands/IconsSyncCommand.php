<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
            try {
                $this->call('blade-fontawesome:sync-icons', ['--pro' => true]);
                $this->info('✓ Pro ikony byly synchronizovány.');
            } catch (\Exception $e) {
                $this->error('✗ Chyba při synchronizaci: ' . $e->getMessage());
                $this->warn('Ujistěte se, že máte nainstalován balíček @fortawesome/fontawesome-pro (npm install).');
                return 1;
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
