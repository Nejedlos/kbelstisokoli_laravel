<?php

namespace App\Console\Commands\Stats;

use App\Services\Stats\Sync\MatchCleanupService;
use Illuminate\Console\Command;

class CleanupDuplicatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cleanup-duplicates {--dry-run : Pokud je true, pouze vypíše, co by udělal.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vyhledá a sloučí duplicitní zápasy v celé databázi.';

    /**
     * Execute the console command.
     */
    public function handle(MatchCleanupService $cleanupService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('--- BĚH NA NEČISTO (DRY RUN) ---');
        }

        $this->info('Spouštím čištění duplicitních zápasů...');

        $stats = $cleanupService->cleanupDuplicates($dryRun);

        $this->table(
            ['Metrika', 'Hodnota'],
            [
                ['Skupiny nalezeny', $stats['groups_found']],
                ['Zápasy sloučeny', $stats['matches_merged']],
                ['Docházka přesunuta', $stats['attendances_moved'] ?? 'N/A'],
            ]
        );

        if ($dryRun) {
            $this->info('Toto byl test. Pro skutečné provedení spusťte bez --dry-run.');
        } else {
            $this->info('Čištění dokončeno.');
        }

        return 0;
    }
}
