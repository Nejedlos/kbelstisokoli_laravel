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

    public $chartSeries = [];

    public $rankings = [];

    public $insights = [];

    public $teamAverages = [];

    public $externalStats = [];

    public $externalMatches = [];

    public $pointsContribution = [];

    public $efficiencyComparison = [];

    public $teamFormSummary = null;

    public $isActiveInSelectedTeam = false;

    public $teamMatchesCount = 0;

    public $statsView = 'avg'; // 'avg' or 'total'

    // Pro týmový pohled (pokud přepnuto na tým)
    public $teamSummary = [];

    public $topScorers = [];

    public $pointsSeries = [];

    public $recentForm = [];

    public $teamLeaders = [];
    public $matchStats = [];

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

        $user = Auth::user();

        // Pokud je teamId null, zkusíme najít nejlepšího kandidáta
        if (! $teamId) {
            // 1. Zkusíme MemberContext
            $teamId = app(\App\Services\Member\MemberContext::class)->getActiveTeamId();
        }

        if (! $teamId && $user) {
            // 2. Zkusíme defaultní tým uživatele
            $teamId = $user->member_default_team_id;
        }

        if (! $teamId && $user) {
            // 3. Zkusíme najít první tým uživatele v dané sezóně
            $teamId = $user->playerProfile?->teams()
                ->wherePivot('is_on_roster', true)
                ->first()?->id;
        }

        if (! $teamId) {
            // 4. Fallback na první tým v systému
            $teamId = Team::first()?->id;
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

    public function loadPlayerStats($userId)
    {
        $this->selectedUserId = $userId;
        $this->view = 'personal';
        $this->loadStats();
    }

    public function loadTeamStats()
    {
        $this->view = 'team';
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

        if (! $this->seasonId) {
            \Log::debug('MyStatistics::loadStats skipping - missing seasonId');
            return;
        }

        // Převedeme prázdný řetězec nebo "all" na null pro teamId (pro logiku "Všechny týmy")
        $teamId = ($this->teamId === 'all' || ! $this->teamId) ? null : (int) $this->teamId;

        $userId = $this->selectedUserId ?? Auth::id();
        $user = $this->selectedUserId ? \App\Models\User::find($userId) : Auth::user();
        $playerProfile = $user?->playerProfile;

        // Detekce aktivity v týmu
        $this->isActiveInSelectedTeam = false;
        if (! $teamId) {
            $this->isActiveInSelectedTeam = true; // Pro "Všechny týmy" zobrazujeme vždy aktuální souhrn (agregovaný)
        } elseif ($playerProfile) {
            $this->isActiveInSelectedTeam = $playerProfile->teams()
                ->where('teams.id', $teamId)
                ->wherePivot('is_on_roster', true)
                ->exists();
        }

        try {
            if ($this->view === 'personal') {
                $service = app(PlayerStatsService::class);

                $this->summary = $service->getSeasonSummary($userId, $this->seasonId, $teamId);

                // Pokud je hráč v týmu aktivní, ale nemá zatím data, vygenerujeme prázdný souhrn s nulami,
                // aby se zobrazily karty (požadavek uživatele na zobrazení i prázdných karet u aktivních týmů).
                if (! $this->summary && $this->isActiveInSelectedTeam) {
                    $this->summary = [
                        'gp' => 0,
                        'pts_total' => 0,
                        'ppg' => 0,
                        'minutes_avg' => 0,
                        'efficiency_avg' => 0,
                        'rebounds_avg' => 0,
                        'assists_avg' => 0,
                        'steals_avg' => 0,
                        'blocks_avg' => 0,
                        'fg2_avg' => 0,
                        'fg3_avg' => 0,
                        'ft_avg' => 0,
                        'fouls_avg' => 0,
                        'is_empty' => true,
                    ];
                }

                $series = $service->getPerGameSeries($userId, $this->seasonId, $teamId);

                // Data pro graf (VŽDY chronologicky)
                $this->chartSeries = $series->sortBy('date')->values()->toArray();

                // Řazení pro osobní zápisy (podle aktuálního filtru tabulky)
                $this->perGameSeries = $series->sortBy(function($item) {
                    if ($this->sortField === 'date') return $item['date'];
                    if ($this->sortField === 'opponent') return $item['opponent'];
                    return $item['values'][$this->sortField] ?? 0;
                }, SORT_REGULAR, $this->sortDirection === 'desc')->values()->toArray();

                // Počet odehraných zápasů týmu v sezóně (pro indikátor 15/18)
                if ($teamId) {
                    $this->teamMatchesCount = \App\Models\BasketballMatch::where('team_id', $teamId)
                        ->where('season_id', $this->seasonId)
                        ->whereNotNull('score_home') // Jen odehrané zápasy
                        ->count();
                } else {
                    // Pokud jsou "Všechny týmy", sečteme unikátní zápasy všech týmů uživatele v sezóně
                    $userTeamsIds = $user?->playerProfile?->teams()->wherePivot('is_on_roster', true)->pluck('teams.id')->toArray() ?? [];
                    if (!empty($userTeamsIds)) {
                        $this->teamMatchesCount = \App\Models\BasketballMatch::whereIn('team_id', $userTeamsIds)
                            ->where('season_id', $this->seasonId)
                            ->whereNotNull('score_home')
                            ->count();
                    } else {
                        $this->teamMatchesCount = 0;
                    }
                }

                if ($teamId) {
                    $this->rankings = $service->getRankings($userId, $this->seasonId, $teamId);
                    $this->insights = $service->getInsights($userId, $this->seasonId, $teamId);
                    $this->teamAverages = $service->getTeamAverages($this->seasonId, $teamId);
                } else {
                    $this->rankings = [];
                    $this->insights = [];
                    $this->teamAverages = [];
                }

                // Načtení externích statistik z cz.basketball
                $externalStatsQuery = \App\Models\ExternalPlayerStat::where('user_id', $userId);
                $externalMatchesQuery = \App\Models\ExternalPlayerMatch::where('user_id', $userId);

                if ($teamId) {
                    $team = Team::find($teamId);
                    if ($team) {
                        $teamNames = [];
                        // Získáme všechny překlady názvu týmu (často cz.basketball používá české názvy)
                        foreach ($team->getTranslations('name') as $name) {
                            $teamNames[] = $name;
                        }

                        $baseNames = [];
                        foreach (array_unique($teamNames) as $name) {
                            $baseNames[] = $name;
                            // Pokud název končí jedním písmenem (např. Sokol Kbely C), získáme i základ (Sokol Kbely)
                            // aby se zobrazila historie celého klubu
                            $stripped = preg_replace('/\s+[A-Z]$/i', '', $name);
                            if ($stripped !== $name) {
                                $baseNames[] = $stripped;
                            }
                        }

                        $externalStatsQuery->where(function ($q) use ($baseNames) {
                            foreach (array_unique($baseNames) as $name) {
                                $q->orWhere('team_name', 'like', '%'.$name.'%');
                            }
                        });

                        // U zápasů filtrujeme přes vazbu na BasketballMatch (pro aktuální tým)
                        // NEBO přes názvy týmu v metadatech (pro historii stejného klubu)
                        $externalMatchesQuery->where(function ($q) use ($teamId, $baseNames) {
                            $q->whereHas('basketballMatch', function ($mq) use ($teamId) {
                                $mq->where('team_id', $teamId);
                            })
                            ->orWhere('metadata', 'like', '%"team_id":' . (int)$teamId . '%')
                            ->orWhere(function($oq) use ($baseNames) {
                                foreach (array_unique($baseNames) as $name) {
                                    $oq->orWhere('metadata', 'like', '%"home_team":"%'.$name.'%"%')
                                       ->orWhere('metadata', 'like', '%"away_team":"%'.$name.'%"%');
                                }
                            });
                        });
                    }
                }

                $this->externalStats = $externalStatsQuery
                    ->orderBy('is_career_total', 'asc')
                    ->orderBy('season_label', 'desc')
                    ->get()
                    ->toArray();

                $this->externalMatches = $externalMatchesQuery
                    ->orderBy('match_date', 'desc')
                    ->get()
                    ->toArray();
            } elseif ($this->view === 'team') {
                if ($teamId) {
                    $service = app(TeamStatsService::class);
                    $this->teamSummary = $service->getSeasonSummary($teamId, $this->seasonId);

                    $allPlayers = $service->getAllPlayersStats($teamId, $this->seasonId);

                    // Řazení
                    $this->topScorers = $allPlayers->sortBy(function($item) {
                        return $item[$this->sortField];
                    }, SORT_REGULAR, $this->sortDirection === 'desc')->values()->toArray();

                    $this->pointsSeries = $service->getPointsSeries($teamId, $this->seasonId)->toArray();
                    $formData = $service->getRecentForm($teamId, $this->seasonId);
                    $this->recentForm = $formData['matches'];
                    $this->teamFormSummary = $formData['count'] > 0 ? [
                        'avg_pts_for' => $formData['avg_pts_for'],
                        'avg_pts_against' => $formData['avg_pts_against'],
                        'count' => $formData['count']
                    ] : null;
                    $this->teamLeaders = $service->getTeamLeaders($teamId, $this->seasonId);
                    $this->pointsContribution = $service->getPointsDistribution($teamId, $this->seasonId);
                    $this->efficiencyComparison = $allPlayers->sortByDesc('efficiency_avg')->take(10)->values()->toArray();
                } else {
                    $this->teamSummary = [];
                    $this->topScorers = [];
                    $this->pointsSeries = [];
                    $this->recentForm = [];
                    $this->teamLeaders = [];
                    $this->pointsContribution = [];
                    $this->efficiencyComparison = [];
                }
            } elseif ($this->view === 'matches') {
                if ($teamId) {
                    $service = app(TeamStatsService::class);
                    $this->teamSummary = $service->getSeasonSummary($teamId, $this->seasonId);
                    $this->matchStats = $service->getMatchStats($teamId, $this->seasonId);
                    $this->pointsSeries = $service->getPointsSeries($teamId, $this->seasonId)->toArray();

                    $formData = $service->getRecentForm($teamId, $this->seasonId);
                    $this->recentForm = $formData['matches'];
                    $this->teamFormSummary = $formData['count'] > 0 ? [
                        'avg_pts_for' => $formData['avg_pts_for'],
                        'avg_pts_against' => $formData['avg_pts_against'],
                        'count' => $formData['count']
                    ] : null;
                } else {
                    $this->teamSummary = [];
                    $this->matchStats = [];
                    $this->pointsSeries = [];
                    $this->recentForm = [];
                    $this->teamFormSummary = null;
                }

                $query = \App\Models\BasketballMatch::query()
                    ->where('season_id', $this->seasonId);

                if ($teamId) {
                    $query->where(function ($query) use ($teamId) {
                        $query->where('team_id', $teamId)
                            ->orWhereHas('teams', function ($q) use ($teamId) {
                                $q->where('teams.id', $teamId);
                            });
                    });
                } else {
                    // Pokud jsou "Všechny týmy", filtrujeme podle všech týmů uživatele
                    $user = $this->selectedUserId ? \App\Models\User::find($this->selectedUserId) : Auth::user();
                    $userTeamsIds = $user?->playerProfile?->teams()->pluck('teams.id')->toArray() ?? [];
                    $query->whereIn('team_id', $userTeamsIds);
                }

                $query->with(['opponent']);

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
        $userId = $this->selectedUserId ?? Auth::id();
        $user = \App\Models\User::find($userId);
        $playerProfile = $user?->playerProfile;
        $teamIdInt = ($this->teamId === 'all' || ! $this->teamId) ? null : (int) $this->teamId;

        // --- DYNAMICKÉ FILTRY (CHYTRÉ) ---

        // 1. Získání sezón pro aktuální kontext (tým nebo hráč)
        $allSeasons = Season::orderBy('name', 'desc')->get();
        $activeSeasonIds = [];

        if ($teamIdInt) {
            // Sezóny, kde má daný tým data nebo zápasy
            $activeSeasonIds = \App\Models\StatisticRow::where('team_id', $teamIdInt)
                ->distinct()->pluck('season_id')->toArray();
            $matchSeasonIds = \App\Models\BasketballMatch::where('team_id', $teamIdInt)
                ->distinct()->pluck('season_id')->toArray();
            $activeSeasonIds = array_unique(array_merge($activeSeasonIds, $matchSeasonIds));
        } else {
            // Sezóny, kde má daný uživatel data (souhrny nebo boxscore)
            $activeSeasonIds = \App\Models\StatisticRow::where('player_id', $userId)
                ->distinct()->pluck('season_id')->toArray();
            // Plus sezóny z external matches (přes spárované interní zápasy)
            $extActiveSeasonIds = \App\Models\ExternalPlayerMatch::where('user_id', $userId)
                ->whereNotNull('basketball_match_id')
                ->join('matches', 'new_external_player_matches.basketball_match_id', '=', 'matches.id')
                ->distinct()->pluck('season_id')->toArray();
            $activeSeasonIds = array_unique(array_merge($activeSeasonIds, $extActiveSeasonIds));

            // Fallback na externí staty (historie v profilu cz.basketball)
            $externalStatSeasons = \App\Models\ExternalPlayerStat::where('user_id', $userId)
                ->pluck('season_label')->toArray();
            foreach($allSeasons as $s) {
                if (in_array($s->id, $activeSeasonIds)) continue;
                $normalized = Season::normalizeName($s->name);
                foreach($externalStatSeasons as $label) {
                    if (str_contains($label, $normalized)) {
                        $activeSeasonIds[] = $s->id;
                        break;
                    }
                }
            }
        }

        $seasonsForDropdown = $allSeasons->map(function($s) use ($activeSeasonIds) {
            $s->has_data = in_array($s->id, $activeSeasonIds);
            return $s;
        });

        // 2. Získání týmů pro vybranou sezónu
        $teamsInSeasonIds = \App\Models\StatisticRow::where('season_id', $this->seasonId)
            ->distinct()->pluck('team_id')->toArray();
        $matchTeamIds = \App\Models\BasketballMatch::where('season_id', $this->seasonId)
            ->distinct()->pluck('team_id')->toArray();
        $teamsWithData = array_unique(array_merge($teamsInSeasonIds, $matchTeamIds));

        $allTeams = Team::orderBy('name')->get()->map(function($t) use ($teamsWithData) {
            $t->has_data = in_array($t->id, $teamsWithData);
            return $t;
        });

        $userTeamsInSeason = collect();
        if ($playerProfile) {
            // Týmy uživatele v dané sezóně (podle soupisky)
            // POZOR: pivot tabulka nemá season_id, takže filtrujeme podle aktivního týmu v dané sezóně přes zápasy nebo staty
            $userTeamsInSeason = $playerProfile->teams()
                ->wherePivot('is_on_roster', true)
                ->get();

            // Pokud chceme být extra chytří, můžeme označit ty, kde má hráč v dané sezóně data
            $userTeamsInSeason = $userTeamsInSeason->map(function($t) use ($userId) {
                $hasData = \App\Models\StatisticRow::where('player_id', $userId)
                    ->where('team_id', $t->id)
                    ->where('season_id', $this->seasonId)
                    ->exists();
                $t->has_data_in_season = $hasData;
                return $t;
            });
        }

        return view('livewire.member.my-statistics', [
            'seasons' => $seasonsForDropdown,
            'allTeams' => $allTeams,
            'userTeams' => $userTeamsInSeason,
            'activeSeasonName' => Season::find($this->seasonId)?->name ?? '?',
            'activeTeamName' => ($this->teamId === 'all' || ! $this->teamId) ? __('Všechny týmy') : (Team::find($this->teamId)?->name ?? '?'),
        ]);
    }
}
