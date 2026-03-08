<?php

namespace App\Jobs\Stats;

use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTeamSeasonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 300; // 5 minut pro celou synchronizaci sezóny týmu

    /**
     * @param  array  $options  [limit, force]
     */
    public function __construct(
        protected int $teamId,
        protected int $seasonId,
        protected array $options = []
    ) {}

    public function handle(ExternalStatsSyncService $service): void
    {
        $service->syncTeamSeason($this->teamId, $this->seasonId, $this->options);
    }
}
