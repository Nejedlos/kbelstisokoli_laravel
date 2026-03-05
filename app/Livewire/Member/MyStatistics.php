<?php

namespace App\Livewire\Member;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\PlayerStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyStatistics extends Component
{
    public $seasonId;

    public $teamId;

    public $view = 'personal'; // 'personal' or 'team'

    public $summary = [];

    public $perGameSeries = [];

    public $rankings = [];

    public $insights = [];

    public $teamAverages = [];

    // Pro týmový pohled (pokud přepnuto na tým)
    public $teamSummary = [];

    public $topScorers = [];

    public $pointsSeries = [];

    public $recentForm = [];

    public function mount()
    {
        $this->seasonId = Season::where('is_active', true)->first()?->id ?? Season::latest()->first()?->id;

        // Zkusíme najít první tým uživatele v dané sezóně
        $user = Auth::user();

        if (! $this->teamId) {
            $this->teamId = $user->playerProfile?->teams()
                ->wherePivot('is_on_roster', true)
                ->first()?->id ?? Team::first()?->id;
        }

        $this->loadStats();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['seasonId', 'teamId', 'view'])) {
            $this->loadStats();
        }
    }

    public function loadStats()
    {
        if (! $this->seasonId || ! $this->teamId) {
            return;
        }

        if ($this->view === 'personal') {
            $service = app(PlayerStatsService::class);
            $userId = Auth::id();

            $this->summary = $service->getSeasonSummary($userId, $this->seasonId, $this->teamId);
            $this->perGameSeries = $service->getPerGameSeries($userId, $this->seasonId, $this->teamId)->toArray();
            $this->rankings = $service->getRankings($userId, $this->seasonId, $this->teamId);
            $this->insights = $service->getInsights($userId, $this->seasonId, $this->teamId);
            $this->teamAverages = $service->getTeamAverages($this->seasonId, $this->teamId);
        } else {
            $service = app(TeamStatsService::class);
            $this->teamSummary = $service->getSeasonSummary($this->teamId, $this->seasonId);
            $this->topScorers = $service->getTopScorers($this->teamId, $this->seasonId)->toArray();
            $this->pointsSeries = $service->getPointsSeries($this->teamId, $this->seasonId)->toArray();
            $this->recentForm = $service->getRecentForm($this->teamId, $this->seasonId)->toArray();
        }

        $this->dispatch('statsLoaded');
    }

    public function setView($view)
    {
        $this->view = $view;
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.member.my-statistics', [
            'seasons' => Season::orderBy('name', 'desc')->get(),
            'allTeams' => Team::all(),
            'userTeams' => Auth::user()->playerProfile?->teams ?? collect(),
        ]);
    }
}
