<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Stats\Sync\PlayerSyncService;
use Illuminate\Console\Command;

class StatsSyncPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-players {--user_id= : Synchronizovat pouze konkrétního uživatele}
                            {--team_id= : Synchronizovat pouze hráče z daného týmu}
                            {--force : Vynutit synchronizaci i bez změn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje detaily hráčů (fotky, historii) z externích zdrojů (cz.basketball)';

    /**
     * Execute the console command.
     */
    public function handle(PlayerSyncService $syncService)
    {
        $this->info('Zahajuji synchronizaci detailů hráčů...');

        $query = User::query();

        // Filtrujeme pouze ty, kteří mají externí mapování na czbasketball
        $query->whereHas('externalMappings', function ($q) {
            $q->where('source_key', 'czbasketball');
        });

        if ($userId = $this->option('user_id')) {
            $query->where('id', $userId);
        }

        if ($teamId = $this->option('team_id')) {
            $query->whereHas('teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Nebyli nalezeni žádní hráči s externím mapováním.');
            return 0;
        }

        $this->info("Nalezeno {$users->count()} hráčů k synchronizaci.");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $successCount = 0;
        foreach ($users as $user) {
            $result = $syncService->syncPlayer($user, ['force' => $this->option('force')]);
            if ($result) {
                $successCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Synchronizace dokončena. Úspěšně: {$successCount}, Selhalo: " . ($users->count() - $successCount));

        return 0;
    }
}
