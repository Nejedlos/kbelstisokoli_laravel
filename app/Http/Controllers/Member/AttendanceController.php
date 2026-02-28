<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Training;
use App\Models\UserSeasonConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $now = now();
        $currentSeasonId = Season::where('is_active', true)->first()?->id;

        // Načteme ID uživatelů, kterým se hlídá docházka v této sezóně
        $trackedUserIds = $currentSeasonId
            ? UserSeasonConfig::where('season_id', $currentSeasonId)->where('track_attendance', true)->pluck('user_id')->toArray()
            : [];

        $trainings = Training::with([
            'teams.activePlayers',
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->withCount([
                'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('starts_at', '>=', $now)
            ->orderBy('starts_at')
            ->get()
            ->map(function ($item) use ($trackedUserIds) {
                // Počet lidí, od kterých se čeká odpověď (jsou v týmu a jsou trackovaní)
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
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->withCount([
                'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->get()
            ->map(function ($item) use ($currentSeasonId) {
                // U zápasu může být jiná sezóna než aktuální, ale většinou je to stejné
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
            ->get()
            ->map(function ($item) use ($trackedUserIds) {
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

        $program = $trainings->concat($matches)->concat($events)->sortBy('time');

        return view('member.attendance.index', [
            'program' => $program,
        ]);
    }

    public function show(string $type, int $id): View
    {
        $user = auth()->user();
        $now = now();

        $modelClass = match ($type) {
            'training' => Training::class,
            'match' => BasketballMatch::class,
            'event' => ClubEvent::class,
            default => abort(404),
        };

        // Načteme událost se všemi relacemi
        $query = $modelClass::with([]);

        if ($type === 'training') {
            $query->with(['teams.activePlayers.user']);
        } elseif ($type === 'match') {
            $query->with(['team.activePlayers.user', 'opponent', 'season']);
        } elseif ($type === 'event') {
            $query->with(['teams.activePlayers.user']);
        }

        $item = $query->findOrFail($id);

        // Zjistíme sezónu pro kontrolu track_attendance
        $seasonId = ($type === 'match') ? $item->season_id : Season::where('is_active', true)->first()?->id;

        // Načteme všechny docházky pro tuto událost
        $allAttendances = Attendance::with('user')
            ->where('attendable_id', $id)
            ->where('attendable_type', $modelClass)
            ->get();

        // Získáme seznam všech unikátních aktivních hráčů, kteří jsou v týmu
        $teams = collect();
        if ($type === 'match') {
            if ($item->team) {
                $teams->push($item->team);
            }
        } else {
            $teams = $item->teams;
        }

        $allTeamUsers = collect();
        foreach ($teams as $team) {
            if (! $team) {
                continue;
            }
            foreach ($team->activePlayers as $profile) {
                if ($profile->user) {
                    $allTeamUsers->put($profile->user_id, $profile->user);
                }
            }
        }

        // Zjistíme, u kterých hráčů se hlídá docházka (track_attendance)
        $usersToTrack = collect();
        if ($seasonId) {
            $usersToTrack = UserSeasonConfig::where('season_id', $seasonId)
                ->whereIn('user_id', $allTeamUsers->keys())
                ->where('track_attendance', true)
                ->pluck('user_id');
        }

        // Rozdělíme uživatele do skupin
        $confirmed = collect();
        $declined = collect();
        $maybe = collect();
        $pending = collect();

        // 1. Nejprve přidáme ty, kteří už odpověděli (bez ohledu na to, zda se jim hlídá docházka)
        foreach ($allAttendances as $att) {
            $status = $att->planned_status;
            $playerData = [
                'user' => $att->user,
                'attendance' => $att,
                'is_me' => $att->user_id === $user->id,
            ];

            if ($status === 'confirmed') {
                $confirmed->put($att->user_id, $playerData);
            } elseif ($status === 'declined') {
                $declined->put($att->user_id, $playerData);
            } elseif ($status === 'maybe') {
                $maybe->put($att->user_id, $playerData);
            }
        }

        // 2. Přidáme ty, kteří neodpověděli, ale HLÍDÁ se jim docházka (to jsou ti s otazníkem)
        foreach ($usersToTrack as $userId) {
            // Pokud už nejsou v potvrzených/omluvených/možná
            if (! $confirmed->has($userId) && ! $declined->has($userId) && ! $maybe->has($userId)) {
                $pending->put($userId, [
                    'user' => $allTeamUsers->get($userId),
                    'attendance' => null,
                    'is_me' => $userId === $user->id,
                ]);
            }
        }

        // Odpověď aktuálního uživatele pro formulář v detailu
        $myAttendance = $allAttendances->where('user_id', $user->id)->first();

        return view('member.attendance.show', [
            'type' => $type,
            'item' => $item,
            'confirmed' => $confirmed->values(),
            'declined' => $declined->values(),
            'maybe' => $maybe->values(),
            'pending' => $pending->values(),
            'myAttendance' => $myAttendance,
            'time' => $type === 'match' ? $item->scheduled_at : $item->starts_at,
        ]);
    }

    public function history(Request $request): View
    {
        $user = auth()->user();

        $attendances = Attendance::with('attendable')
            ->where('user_id', $user->id)
            ->orderBy('responded_at', 'desc')
            ->paginate(20);

        return view('member.attendance.history', [
            'attendances' => $attendances,
        ]);
    }

    public function store(Request $request, string $type, int $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:confirmed,declined,maybe',
            'note' => 'nullable|string|max:255',
            'excuse_reason' => 'nullable|string',
        ]);

        $modelClass = match ($type) {
            'training' => Training::class,
            'match' => BasketballMatch::class,
            'event' => ClubEvent::class,
            default => abort(404),
        };

        $item = $modelClass::findOrFail($id);

        $note = $request->note;
        if ($request->status === 'declined' && $request->excuse_reason) {
            $reasonLabel = __('member.attendance.excuse_reasons.'.$request->excuse_reason);
            // Zkombinujeme vybraný důvod s textovou poznámkou
            if ($note) {
                $note = $reasonLabel.' ('.$note.')';
            } else {
                $note = $reasonLabel;
            }
        }

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'attendable_id' => $item->id,
                'attendable_type' => $modelClass,
            ],
            [
                'planned_status' => $request->status,
                'note' => $note,
                'responded_at' => now(),
            ]
        );

        event(new \App\Events\RsvpChanged($attendance));

        return back()->with('status', 'Vaše odpověď byla uložena.');
    }
}
