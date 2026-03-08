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

        $successCount = 0;
        foreach ($users as $user) {
            if (ConsoleService::isStopRequested()) {
                ConsoleService::log("Synchronizace přerušena (stop flag).", 'warning');
                break;
            }

            ConsoleService::log("- Synchronizuji hráče: {$user->name} (#{$user->id})");
            try {
                $result = $syncService->syncPlayer($user, ['force' => $this->options['force'] ?? false]);
                if ($result) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                ConsoleService::log("Chyba při synchronizaci hráče #{$user->id}: " . $e->getMessage(), 'error');
            }
        }

        ConsoleService::log("Synchronizace hráčů dokončena. Úspěšně: {$successCount}, Celkem: " . $users->count());
    }
}
