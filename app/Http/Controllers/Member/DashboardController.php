<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use App\Models\Season;
use App\Models\UserSeasonConfig;
use App\Services\Finance\FinanceService;
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

        // Doplňková data pro moderní nástěnku
        $economySummary = app(FinanceService::class)->getMemberSummary($user);
        $notifications = $user->notifications()->latest()->limit(5)->get();
        $avatarUrl = method_exists($user, 'getAvatarUrl') ? $user->getAvatarUrl('thumb') : null;

        $currentSeasonId = Season::where('is_active', true)->first()?->id;
        $trackedUserIds = $currentSeasonId
            ? UserSeasonConfig::where('season_id', $currentSeasonId)->where('track_attendance', true)->pluck('user_id')->toArray()
            : [];

        // 1. Nejbližší akce (limit 3)
        $trainings = Training::with([
                'teams.activePlayers',
                'attendances' => fn($q) => $q->where('user_id', $user->id)
            ])
            ->withCount([
                'attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('starts_at', '>=', $now)
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(function($item) use ($trackedUserIds) {
                $expectedIds = collect();
                foreach ($item->teams as $team) {
                    foreach ($team->activePlayers as $profile) {
                        if (in_array($profile->user_id, $trackedUserIds)) {
                            $expectedIds->push($profile->user_id);
                        }
                    }
                }
                $item->expected_players_count = $expectedIds->unique()->count();
                return ['type' => 'training', 'data' => $item, 'time' => $item->starts_at];
            });

        $matches = BasketballMatch::with([
                'team.activePlayers',
                'opponent',
                'attendances' => fn($q) => $q->where('user_id', $user->id)
            ])
            ->withCount([
                'attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get()
            ->map(function($item) use ($currentSeasonId) {
                $seasonId = $item->season_id ?: $currentSeasonId;
                $trackedIds = UserSeasonConfig::where('season_id', $seasonId)->where('track_attendance', true)->pluck('user_id')->toArray();

                $expectedIds = collect();
                if ($item->team) {
                    foreach ($item->team->activePlayers as $profile) {
                        if (in_array($profile->user_id, $trackedIds)) {
                            $expectedIds->push($profile->user_id);
                        }
                    }
                }
                $item->expected_players_count = $expectedIds->unique()->count();
                return ['type' => 'match', 'data' => $item, 'time' => $item->scheduled_at];
            });

        $events = ClubEvent::with([
                'teams.activePlayers',
                'attendances' => fn($q) => $q->where('user_id', $user->id)
            ])
            ->withCount([
                'attendances as confirmed_count' => fn($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('starts_at', '>=', $now)
            ->where('rsvp_enabled', true)
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(function($item) use ($trackedUserIds) {
                $expectedIds = collect();
                foreach ($item->teams as $team) {
                    foreach ($team->activePlayers as $profile) {
                        if (in_array($profile->user_id, $trackedUserIds)) {
                            $expectedIds->push($profile->user_id);
                        }
                    }
                }
                $item->expected_players_count = $expectedIds->unique()->count();
                return ['type' => 'event', 'data' => $item, 'time' => $item->starts_at];
            });

        $upcoming = $trainings->concat($matches)->concat($events)->sortBy('time')->take(3);

        // 2. Počty pro KPI (pouze akce, kde se po uživateli HLÍDÁ docházka a ještě neodpověděl)
        $pendingCount = 0;
        foreach ($upcoming as $item) {
            $data = $item['data'];
            $type = $item['type'];

            // Zjistíme, zda se pro tuto akci po tomto uživateli hlídá docházka
            $seasonId = ($type === 'match') ? $data->season_id : $currentSeasonId;
            $isTracked = UserSeasonConfig::where('season_id', $seasonId)
                ->where('user_id', $user->id)
                ->where('track_attendance', true)
                ->exists();

            if ($isTracked) {
                $att = $data->attendances->first();
                if (!$att || $att->planned_status === 'pending') {
                    $pendingCount++;
                }
            }
        }

        // 3. Týmy uživatele
        $myTeams = $user->playerProfile?->teams ?? collect();

        return view('member.dashboard', [
            'upcoming' => $upcoming,
            'pendingCount' => $pendingCount,
            'myTeams' => $myTeams,
            'economySummary' => $economySummary,
            'notifications' => $notifications,
            'avatarUrl' => $avatarUrl,
            'user' => $user,
        ]);
    }
}
