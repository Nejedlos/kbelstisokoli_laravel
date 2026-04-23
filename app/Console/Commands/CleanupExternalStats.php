<?php

namespace App\Console\Commands;

use App\Models\ExternalImportRun;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExternalStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'external-stats:cleanup {--days=30 : Počet dní pro uchování dat} {--runs-months=3 : Počet měsíců pro uchování historie běhů}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promaže staré snapshoty a historii běhů externích importů.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $runsMonths = (int) $this->option('runs-months');
        $cutoffDate = now()->subDays($days);
        $runsCutoffDate = now()->subMonths($runsMonths);

        $this->info("Zahajuji úklid starších dat než {$cutoffDate->toDateString()}...");

        // 1. Úklid historie běhů
        $deletedRuns = ExternalImportRun::where('created_at', '<', $runsCutoffDate)->delete();
        $this->info("Smazáno {$deletedRuns} historických běhů starších než {$runsMonths} měsíce.");

        // 2. Úklid snapshotů (pouze success/skipped)
        // Musíme prohledat složky a smazat soubory, které nejsou v metadata stále relevantních/neúspěšných běhů.
        // Nebo jednoduše promazat složky a zachovat ty, které jsou u posledních neúspěšných běhů.

        $files = Storage::disk('local')->allFiles('external/czbasketball');
        $deletedFiles = 0;

        // Načteme cesty k snapshotům, které chceme zachovat (z neúspěšných běhů)
        $importantSnapshotPaths = ExternalImportRun::whereIn('status', ['failed', 'partial_failed'])
            ->get()
            ->map(fn($run) => $run->metadata['snapshot_path'] ?? null)
            ->filter()
            ->unique()
            ->toArray();

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));

            if ($lastModified->lt($cutoffDate)) {
                // Je to staré, ale je to poslední fail?
                $isImportant = in_array($file, $importantSnapshotPaths);

                if (! $isImportant) {
                    Storage::disk('local')->delete($file);
                    $deletedFiles++;
                }
            }
        }

        $this->info("Smazáno {$deletedFiles} starých HTML snapshotů.");

        return self::SUCCESS;
    }
}
