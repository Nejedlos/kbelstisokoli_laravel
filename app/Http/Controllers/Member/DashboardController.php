<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Training;
use App\Models\UserSeasonConfig;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $locale = app()->getLocale();

        $cacheKey = "member_dashboard_{$user->id}_{$locale}";
        $data = Cache::remember($cacheKey, 600, function () use ($user, $now) {
            // Doplňková data pro moderní nástěnku
            $economySummary = app(FinanceService::class)->getMemberSummary($user);
            $notifications = $user->notifications()->latest()->limit(5)->get();
            $avatarUrl = method_exists($user, 'getAvatarUrl') ? $user->getAvatarUrl('thumb') : null;

            $currentSeasonId = Season::where('is_active', true)->first()?->id;

            // Cache pro trackedUserIds napříč různými sezónami, pokud by se lišily, ale většinou nás zajímá aktuální
            $trackedUserIds = $currentSeasonId
                ? Cache::remember("tracked_user_ids_{$currentSeasonId}", 3600, function () use ($currentSeasonId) {
                    return UserSeasonConfig::where('season_id', $currentSeasonId)
                        ->where('track_attendance', true)
                        ->pluck('user_id')
                        ->toArray();
                })
                : [];

            // 1. Nejbližší akce (limit 3)
            $trainings = Training::with([
                'teams.activePlayers:player_profiles.id,user_id', // Načteme jen to nejdůležitější pro výpočet
                'attendances' => fn ($q) => $q->where('user_id', $user->id),
            ])
                ->withCount([
                    'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                    'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                    'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
                ])
                ->where('starts_at', '>=', $now)
                ->orderBy('starts_at')
                ->limit(3)
                ->get()
                ->map(function ($item) use ($trackedUserIds) {
                    $expectedIds = [];
                    foreach ($item->teams as $team) {
                        foreach ($team->activePlayers as $profile) {
                            if (in_array($profile->user_id, $trackedUserIds)) {
                                $expectedIds[] = $profile->user_id;
                            }
                        }
                    }
                    $item->expected_players_count = count(array_unique($expectedIds));

                    return ['type' => 'training', 'data' => $item, 'time' => $item->starts_at];
                });

            $matches = BasketballMatch::with([
                'team.activePlayers:player_profiles.id,user_id',
                'opponent',
                'attendances' => fn ($q) => $q->where('user_id', $user->id),
            ])
                ->withCount([
                    'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                    'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                    'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
                ])
                ->where('scheduled_at', '>=', $now)
                ->orderBy('scheduled_at')
                ->limit(3)
                ->get()
                ->map(function ($item) use ($currentSeasonId, $trackedUserIds) {
                    $seasonId = $item->season_id ?: $currentSeasonId;

                    // Pokud je sezóna jiná než aktuální (což u budoucích zápasů je málo pravděpodobné, ale možné)
                    $seasonTrackedIds = ($seasonId == $currentSeasonId) ? $trackedUserIds : Cache::remember("tracked_user_ids_{$seasonId}", 3600, function () use ($seasonId) {
                        return UserSeasonConfig::where('season_id', $seasonId)
                            ->where('track_attendance', true)
                            ->pluck('user_id')
                            ->toArray();
                    });

                    $expectedIds = [];
                    if ($item->team) {
                        foreach ($item->team->activePlayers as $profile) {
                            if (in_array($profile->user_id, $seasonTrackedIds)) {
                                $expectedIds[] = $profile->user_id;
                            }
                        }
                    }
                    $item->expected_players_count = count(array_unique($expectedIds));

                    return ['type' => 'match', 'data' => $item, 'time' => $item->scheduled_at];
                });

            $events = ClubEvent::with([
                'teams.activePlayers:player_profiles.id,user_id',
                'attendances' => fn ($q) => $q->where('user_id', $user->id),
            ])
                ->withCount([
                    'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                    'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                    'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
                ])
                ->where('starts_at', '>=', $now)
                ->where('rsvp_enabled', true)
                ->orderBy('starts_at')
                ->limit(3)
                ->get()
                ->map(function ($item) use ($trackedUserIds) {
                    $expectedIds = [];
                    foreach ($item->teams as $team) {
                        foreach ($team->activePlayers as $profile) {
                            if (in_array($profile->user_id, $trackedUserIds)) {
                                $expectedIds[] = $profile->user_id;
                            }
                        }
                    }
                    $item->expected_players_count = count(array_unique($expectedIds));

                    return ['type' => 'event', 'data' => $item, 'time' => $item->starts_at];
                });

            $upcoming = $trainings->concat($matches)->concat($events)->sortBy('time')->take(3);

            // 2. Počty pro KPI (pouze akce, kde se po uživateli HLÍDÁ docházka a ještě neodpověděl)
            $pendingCount = 0;
            foreach ($upcoming as $item) {
                $itemData = $item['data'];
                $itemType = $item['type'];

                // Zjistíme, zda se pro tuto akci po tomto uživateli hlídá docházka
                $seasonId = ($itemType === 'match') ? $itemData->season_id : $currentSeasonId;
                $seasonId = $seasonId ?: $currentSeasonId;

                $isTracked = in_array($user->id, ($seasonId == $currentSeasonId) ? $trackedUserIds : Cache::remember("tracked_user_ids_{$seasonId}", 3600, function () use ($seasonId) {
                    return UserSeasonConfig::where('season_id', $seasonId)->where('track_attendance', true)->pluck('user_id')->toArray();
                }));

                if ($isTracked) {
                    $att = $itemData->attendances->first();
                    if (! $att || $att->planned_status === 'pending') {
                        $pendingCount++;
                    }
                }
            }

            // 3. Týmy uživatele
            $myTeams = $user->playerProfile?->teams()->get() ?? collect();

            return [
                'upcoming' => $upcoming,
                'pendingCount' => $pendingCount,
                'myTeams' => $myTeams,
                'economySummary' => $economySummary,
                'notifications' => $notifications,
                'avatarUrl' => $avatarUrl,
            ];
        });

        return view('member.dashboard', array_merge($data, [
            'user' => $user,
        ]));
    }
}
