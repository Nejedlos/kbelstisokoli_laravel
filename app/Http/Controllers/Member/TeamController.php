<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\BasketballMatch;
use App\Models\Training;
use App\Models\ClubEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Zobrazí seznam týmů pro trenéra.
     */
    public function index(Request $request): View
    {
        $this->authorize('manage_teams');

        $teams = Team::withCount(['players', 'trainings', 'games'])->get();

        return view('member.teams.index', compact('teams'));
    }

    /**
     * Zobrazí detail týmu s přehledem docházky.
     */
    public function show(Team $team): View
    {
        $this->authorize('manage_teams');

        $upcomingMatches = BasketballMatch::where('team_id', $team->id)
            ->where('scheduled_at', '>=', now())
            ->withCount(['attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed')])
            ->withCount(['attendances as declined_count' => fn($q) => $q->where('planned_status', 'declined')])
            ->orderBy('scheduled_at')
            ->get();

        $upcomingTrainings = Training::whereHas('teams', fn($q) => $q->where('teams.id', $team->id))
            ->where('starts_at', '>=', now())
            ->withCount(['attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed')])
            ->orderBy('starts_at')
            ->get();

        return view('member.teams.show', compact('team', 'upcomingMatches', 'upcomingTrainings'));
    }
}
