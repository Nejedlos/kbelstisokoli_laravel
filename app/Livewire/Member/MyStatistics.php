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

    public $selectedUserId;

    public $sortField = 'pts_total';
    public $sortDirection = 'desc';

    public $matches = [];

    public $summary = [];

    public $perGameSeries = [];

    public $rankings = [];

    public $insights = [];

    public $teamAverages = [];

    public $externalStats = [];

    public $externalMatches = [];

    // Pro týmový pohled (pokud přepnuto na tým)
    public $teamSummary = [];

    public $topScorers = [];

    public $pointsSeries = [];

    public $recentForm = [];

    public function mount($teamId = null, $seasonId = null, $view = null, $userId = null)
    {
        $this->selectedUserId = $userId ?? Auth::id();

        if ($view) {
            $this->view = $view;
        }

        $this->sortField = match($this->view) {
            'team' => 'pts_total',
            'personal' => 'pts',
            'matches' => 'date',
            default => 'pts_total'
        };

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
        if (in_array($propertyName, ['seasonId', 'teamId', 'sortField', 'sortDirection'])) {
            if ($propertyName === 'teamId') {
                app(\App\Services\Member\MemberContext::class)->setActiveTeamId((int)$this->teamId);
            }
            $this->loadStats();
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        $this->loadStats();
    }

    public function showPlayerStats($userId)
    {
        $this->selectedUserId = $userId;
        $this->view = 'personal';
        $this->loadStats();
    }

    public function loadStats()
    {
        $userId = $this->selectedUserId ?? Auth::id();

        \Log::debug('MyStatistics::loadStats starting', [
            'userId' => $userId,
            'seasonId' => $this->seasonId,
            'teamId' => $this->teamId,
            'view' => $this->view
        ]);

        if (! $this->seasonId || ! $this->teamId) {
            \Log::debug('MyStatistics::loadStats skipping - missing seasonId or teamId');
            return;
        }

        try {
            if ($this->view === 'personal') {
                $service = app(PlayerStatsService::class);

                $this->summary = $service->getSeasonSummary($userId, $this->seasonId, $this->teamId);

                $series = $service->getPerGameSeries($userId, $this->seasonId, $this->teamId);

                // Řazení pro osobní zápisy
                $this->perGameSeries = $series->sortBy(function($item) {
                    if ($this->sortField === 'date') return $item['date'];
                    if ($this->sortField === 'opponent') return $item['opponent'];
                    return $item['values'][$this->sortField] ?? 0;
                }, SORT_REGULAR, $this->sortDirection === 'desc')->values()->toArray();

                $this->rankings = $service->getRankings($userId, $this->seasonId, $this->teamId);
                $this->insights = $service->getInsights($userId, $this->seasonId, $this->teamId);
                $this->teamAverages = $service->getTeamAverages($this->seasonId, $this->teamId);

                // Načtení externích statistik z cz.basketball
                $this->externalStats = \App\Models\ExternalPlayerStat::where('user_id', $userId)
                    ->orderBy('is_career_total', 'asc')
                    ->orderBy('season_label', 'desc')
                    ->get()
                    ->toArray();

                $this->externalMatches = \App\Models\ExternalPlayerMatch::where('user_id', $userId)
                    ->orderBy('match_date', 'desc')
                    ->get()
                    ->toArray();
            } elseif ($this->view === 'team') {
                $service = app(TeamStatsService::class);
                $this->teamSummary = $service->getSeasonSummary($this->teamId, $this->seasonId);

                $allPlayers = $service->getAllPlayersStats($this->teamId, $this->seasonId);

                // Řazení
                $this->topScorers = $allPlayers->sortBy(function($item) {
                    return $item[$this->sortField];
                }, SORT_REGULAR, $this->sortDirection === 'desc')->values()->toArray();

                $this->pointsSeries = $service->getPointsSeries($this->teamId, $this->seasonId)->toArray();
                $this->recentForm = $service->getRecentForm($this->teamId, $this->seasonId)->toArray();
            } elseif ($this->view === 'matches') {
                $query = \App\Models\BasketballMatch::query()
                    ->where('season_id', $this->seasonId)
                    ->where(function ($query) {
                        $query->where('team_id', $this->teamId)
                            ->orWhereHas('teams', function ($q) {
                                $q->where('teams.id', $this->teamId);
                            });
                    })
                    ->with(['opponent']);

                // Mapování polí pro řazení zápasů
                $sortField = $this->sortField;
                if ($sortField === 'date') $sortField = 'scheduled_at';

                $this->matches = $query->orderBy($sortField, $this->sortDirection)
                    ->get()
                    ->toArray();
            }
            \Log::debug('MyStatistics::loadStats finished successfully');
        } catch (\Exception $e) {
            \Log::error('MyStatistics::loadStats failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->dispatch('statsLoaded');
    }


    public function render()
    {
        $user = $this->selectedUserId ? \App\Models\User::find($this->selectedUserId) : Auth::user();
        $playerProfile = $user?->playerProfile;

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
            'activeSeasonName' => Season::find($this->seasonId)?->name ?? '?',
            'activeTeamName' => Team::find($this->teamId)?->name ?? '?',
        ]);
    }
}
