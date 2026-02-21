<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = auth()->user();
        $now = now();

        // 1. Nejbližší akce (limit 3)
        $trainings = Training::with(['team', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('starts_at', '>=', $now)
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn($item) => ['type' => 'training', 'data' => $item, 'time' => $item->starts_at]);

        $matches = BasketballMatch::with(['team', 'opponent', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get()
            ->map(fn($item) => ['type' => 'match', 'data' => $item, 'time' => $item->scheduled_at]);

        $events = ClubEvent::with(['team', 'attendances' => fn($q) => $q->where('user_id', $user->id)])
            ->where('starts_at', '>=', $now)
            ->where('rsvp_enabled', true)
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn($item) => ['type' => 'event', 'data' => $item, 'time' => $item->starts_at]);

        $upcoming = $trainings->concat($matches)->concat($events)->sortBy('time')->take(3);

        // 2. Počty pro KPI
        $pendingCount = $trainings->concat($matches)->concat($events)
            ->filter(fn($item) => $item['data']->attendances->isEmpty() || $item['data']->attendances->first()->status === 'pending')
            ->count();

        // 3. Týmy uživatele
        $myTeams = $user->playerProfile?->teams ?? collect();

        // 4. Trenérské údaje (pokud je trenér)
        $coachTeams = [];
        if ($user->can('manage_teams')) {
            // Zde by byla logika pro týmy, které trenér skutečně trénuje.
            // Pro účely skeletonu použijeme všechny týmy nebo placeholder.
            $coachTeams = \App\Models\Team::limit(3)->get();
        }

        return view('member.dashboard', [
            'upcoming' => $upcoming,
            'pendingCount' => $pendingCount,
            'myTeams' => $myTeams,
            'coachTeams' => $coachTeams,
        ]);
    }
}
