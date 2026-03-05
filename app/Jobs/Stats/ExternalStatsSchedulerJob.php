<?php

namespace App\Jobs\Stats;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ExternalStatsSchedulerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected bool $recentOnly = false
    ) {}

    public function handle(): void
    {
        if (! Config::get('external_sources.enabled')) {
            return;
        }

        $activeSeason = Season::where('is_active', true)->first();
        if (! $activeSeason) {
            return;
        }

        $teamSlugs = Config::get('external_sources.czbasketball.teams', []);
        $teams = Team::whereIn('slug', $teamSlugs)->get();

        foreach ($teams as $team) {
            $options = [];
            if ($this->recentOnly) {
                $options['recentOnly'] = true;
                $options['maxMatchDetails'] = Config::get('external_sources.czbasketball.limits.max_match_details_per_run', 10);
            }

            SyncTeamSeasonJob::dispatch($team->id, $activeSeason->id, $options);
        }
    }
}
