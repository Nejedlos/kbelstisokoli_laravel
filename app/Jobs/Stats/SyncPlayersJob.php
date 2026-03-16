<?php

namespace App\Jobs\Stats;

use App\Models\User;
use App\Services\Stats\Sync\PlayerSyncService;
use App\Services\Support\ConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlayersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 600; // 10 minut pro synchronizaci hráčů

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PlayerSyncService $syncService): void
    {
        ConsoleService::log("Spouštím hromadnou synchronizaci hráčů...");
        ConsoleService::resetStop();

        $query = User::query();

        // Filtrujeme pouze ty, kteří mají externí mapování na czbasketball
        $query->whereHas('externalMappings', function ($q) {
            $q->where('source_key', 'czbasketball');
        });

        // Řadíme podle poslední aktualizace profilu (nejstarší první),
        // aby restartovaný job nepokračoval vždy od stejných hráčů.
        $query->leftJoin('player_profiles', 'users.id', '=', 'player_profiles.user_id')
            ->orderBy('player_profiles.updated_at', 'asc')
            ->select('users.*');

        if ($userId = ($this->options['user_id'] ?? null)) {
            $query->where('id', $userId);
        }

        if ($teamId = ($this->options['team_id'] ?? null)) {
            $query->whereHas('teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            ConsoleService::log("Nebyli nalezeni žádní hráči k synchronizaci.", 'warning');
            return;
        }

        ConsoleService::log("Nalezeno {$users->count()} hráčů k synchronizaci.");

        $seasonId = \App\Models\Season::where('is_active', true)->first()?->id ?? 0;
        $batchRun = \App\Models\ExternalImportRun::start('czbasketball', $seasonId, $this->options['team_id'] ?? null, 'player_sync_batch', null);
        $batchRun->updateProgress(0, $users->count(), "Inicializace hromadné synchronizace...");

        $successCount = 0;
        $currentIndex = 0;
        foreach ($users as $user) {
            $currentIndex++;
            if (ConsoleService::isStopped() || $batchRun->isCancelled() || $batchRun->status === 'skipped') {
                ConsoleService::log("Synchronizace přerušena (stop flag nebo zrušeno/přeskočeno uživatelem).", 'warning');
                if ($batchRun->status === 'running') {
                    $batchRun->cancel('Zrušeno uživatelem nebo stop flagem.');
                }
                break;
            }

            ConsoleService::log("- Synchronizuji hráče: {$user->name} (#{$user->id}) [{$currentIndex}/{$users->count()}]");
            $batchRun->updateProgress($currentIndex - 1, $users->count(), "Hráč: {$user->name}");

            try {
                $result = $syncService->syncPlayer($user, [
                    'force' => $this->options['force'] ?? false,
                    'parent_run' => $batchRun,
                    'current_index' => $currentIndex,
                    'total_count' => $users->count(),
                ]);
                if ($result) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                ConsoleService::log("Chyba při synchronizaci hráče #{$user->id}: " . $e->getMessage(), 'error');
                $batchRun->addLog('player_sync_failed', $user, null, null, "Hráč #{$user->id} ({$user->name}) selhal: " . $e->getMessage());
            }
        }

        $batchRun->finish([
            'imported_count' => $successCount,
            'total_count' => $users->count()
        ]);

        ConsoleService::log("Synchronizace hráčů dokončena. Úspěšně: {$successCount}, Celkem: " . $users->count());
    }
}
