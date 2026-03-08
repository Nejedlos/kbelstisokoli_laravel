<?php

namespace App\Livewire\Member;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\PlayerStatsService;
use App\Services\Stats\TeamStatsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyStatistics extends Component
{
    public $seasonId;

    public $teamId;

    public $view = 'personal'; // 'personal', 'team', or 'matches'

    public $matches = [];

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

    public function mount($teamId = null, $seasonId = null)
    {
        $this->seasonId = $seasonId ?? Season::where('is_active', true)->first()?->id ?? Season::latest()->first()?->id;

        // Pokud je teamId null, zkusíme MemberContext
        if (! $teamId) {
            $teamId = app(\App\Services\Member\MemberContext::class)->getActiveTeamId();
        }

        if (! $teamId) {
            // Zkusíme najít první tým uživatele v dané sezóně
            $user = Auth::user();
            $teamId = $user->playerProfile?->teams()
                ->wherePivot('is_on_roster', true)
                ->first()?->id ?? Team::first()?->id;
        }

        $this->teamId = $teamId;

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
        } elseif ($this->view === 'team') {
            $service = app(TeamStatsService::class);
            $this->teamSummary = $service->getSeasonSummary($this->teamId, $this->seasonId);
            $this->topScorers = $service->getTopScorers($this->teamId, $this->seasonId)->toArray();
            $this->pointsSeries = $service->getPointsSeries($this->teamId, $this->seasonId)->toArray();
            $this->recentForm = $service->getRecentForm($this->teamId, $this->seasonId)->toArray();
        } elseif ($this->view === 'matches') {
            $this->matches = \App\Models\BasketballMatch::query()
                ->where('season_id', $this->seasonId)
                ->where(function ($query) {
                    $query->where('team_id', $this->teamId)
                        ->orWhereHas('teams', function ($q) {
                            $q->where('teams.id', $this->teamId);
                        });
                })
                ->with(['opponent'])
                ->orderBy('scheduled_at', 'desc')
                ->get()
                ->toArray();
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
        $user = Auth::user();
        $playerProfile = $user->playerProfile;

        // Získání týmů uživatele v aktuálně vybrané sezóně
        $userTeams = collect();
        if ($playerProfile) {
            $userTeams = $playerProfile->teams()
                ->wherePivot('is_on_roster', true)
                ->get();
        }

        return view('livewire.member.my-statistics', [
            'seasons' => Season::orderBy('name', 'desc')->get(),
            'allTeams' => Team::orderBy('name')->get(),
            'userTeams' => $userTeams,
        ]);
    }
}
