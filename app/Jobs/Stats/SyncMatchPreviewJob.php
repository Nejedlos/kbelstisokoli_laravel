<?php

namespace App\Jobs\Stats;

use App\Services\Stats\Sync\MatchPreviewSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMatchPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 180;

    public $backoff = [30, 60, 120];

    public function __construct(protected int $matchId, protected int $teamId, protected bool $force = false) {}

    public function handle(MatchPreviewSyncService $service): void
    {
        $service->sync($this->matchId, $this->teamId, $this->force);
    }
}
