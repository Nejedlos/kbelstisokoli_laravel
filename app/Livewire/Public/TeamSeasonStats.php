<?php

namespace App\Livewire\Public;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\TeamStatsService;
use Livewire\Component;

class TeamSeasonStats extends Component
{
    public $teamId;
    public $seasonId;

    public $summary = [];
    public $topScorers = [];
    public $pointsSeries = [];
    public $recentForm = [];

    public function mount($teamId = null, $seasonId = null)
    {
        $this->seasonId = $seasonId ?? Season::where('is_active', true)->first()?->id ?? Season::latest()->first()?->id;
        $this->teamId = $teamId ?? Team::first()?->id;

        $this->loadStats();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['seasonId', 'teamId'])) {
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        if (!$this->teamId || !$this->seasonId) return;

        $service = app(TeamStatsService::class);
        $this->summary = $service->getSeasonSummary($this->teamId, $this->seasonId);
        $this->topScorers = $service->getTopScorers($this->teamId, $this->seasonId)->toArray();
        $this->pointsSeries = $service->getPointsSeries($this->teamId, $this->seasonId)->toArray();
        $this->recentForm = $service->getRecentForm($this->teamId, $this->seasonId)->toArray();

        $this->dispatch('statsLoaded');
    }

    public function render()
    {
        return view('livewire.public.team-season-stats', [
            'teams' => Team::all(),
            'seasons' => Season::orderBy('name', 'desc')->get(),
        ]);
    }
}
