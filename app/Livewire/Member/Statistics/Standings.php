<?php

namespace App\Livewire\Member\Statistics;

use App\Models\CompetitionStanding;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Member\MemberContext;
use Livewire\Component;
use Illuminate\Support\Collection;

class Standings extends Component
{
    public ?int $seasonId = null;
    public ?int $teamId = null;

    protected $queryString = [
        'seasonId' => ['except' => null, 'as' => 'season'],
        'teamId' => ['except' => null, 'as' => 'team'],
    ];

    public function mount()
    {
        if (!$this->seasonId) {
            $this->seasonId = Season::where('is_active', true)->first()?->id ?? Season::latest('id')->first()?->id;
        }

        if (!$this->teamId) {
            $this->teamId = app(MemberContext::class)->getActiveTeamId();
        }
    }

    public function getStandingsProperty(): Collection
    {
        if (!$this->seasonId) {
            return collect();
        }

        $query = CompetitionStanding::where('season_id', $this->seasonId);

        if ($this->teamId) {
            $competitionUrls = ExternalTeamSeasonConfig::where('team_id', $this->teamId)
                ->where('season_id', $this->seasonId)
                ->pluck('competition_url')
                ->filter()
                ->unique();

            if ($competitionUrls->isEmpty()) {
                return collect();
            }

            $query->whereIn('competition_url', $competitionUrls);
        }

        return $query->orderBy('rank')->get();
    }

    public function render()
    {
        $seasons = Season::orderByDesc('name')->get();
        $teams = Team::orderBy('name')->get();

        $standings = $this->standings;
        $groupedStandings = $standings->groupBy('competition_url');

        return view('livewire.member.statistics.standings', [
            'seasons' => $seasons,
            'teams' => $teams,
            'groupedStandings' => $groupedStandings,
        ]);
    }
}
