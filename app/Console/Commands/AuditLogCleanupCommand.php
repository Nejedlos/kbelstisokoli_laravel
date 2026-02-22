<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class AuditLogCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup {--days=90 : Počet dní, po kterých se mají logy smazat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Odstraní staré záznamy z audit logu.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $date = now()->subDays($days);

        $count = AuditLog::where('occurred_at', '<', $date)->delete();

        $this->info("Bylo smazáno {$count} starých záznamů z audit logu (starší než {$days} dní).");

        return 0;
    }
}
