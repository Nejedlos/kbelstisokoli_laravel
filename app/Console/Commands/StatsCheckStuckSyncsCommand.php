<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StatsCheckStuckSyncsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:check-stuck-syncs {--minutes=30 : Po kolika minutách bez aktualizace je běh považován za zaseknutý}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detekuje a označí zaseknuté synchronizace z externích zdrojů.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->option('minutes');
        $this->info("Kontroluji synchronizace, které se nepohnuly déle než {$minutes} minut...");

        $staleRuns = \App\Models\ExternalImportRun::where('status', 'running')
            ->where('updated_at', '<', now()->subMinutes($minutes))
            ->get();

        if ($staleRuns->isEmpty()) {
            $this->info('Nenalezeny žádné zaseknuté synchronizace.');
            return self::SUCCESS;
        }

        $this->warn("Nalezeno {$staleRuns->count()} potenciálně zaseknutých běhů.");

        foreach ($staleRuns as $run) {
            $this->line("- Běh ID: {$run->id}, Zdroj: {$run->source_key}, Typ: {$run->run_type}, Poslední aktivita: {$run->updated_at}");
            $run->markAsStuck();
            $this->info("  -> Označeno jako 'stuck'.");
        }

        $this->info('Hotovo.');

        return self::SUCCESS;
    }
}
