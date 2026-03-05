<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Training;
use App\Models\UserSeasonConfig;
use App\Services\Member\MemberContext;
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
        $activeTeamId = app(MemberContext::class)->getActiveTeamId();

        // Parametry filtru
        $filterType = $request->get('type', 'all');
        $filterYear = $request->get('year');
        $filterMonth = $request->get('month');
        $filterAttendance = $request->get('attendance', 'all');

        // Načteme ID uživatelů, kterým se hlídá docházka v této sezóně
        $trackedUserIds = $currentSeasonId
            ? UserSeasonConfig::where('season_id', $currentSeasonId)->where('track_attendance', true)->pluck('user_id')->toArray()
            : [];

        // Příprava dotazů
        $trainingsQuery = Training::with([
            'teams.activePlayers',
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->withCount([
                'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
            ])
            ->when($activeTeamId, fn ($q) => $q->whereHas('teams', fn ($sq) => $sq->where('teams.id', $activeTeamId)))
            ->orderBy('starts_at');

        $matchesQuery = BasketballMatch::with([
            'team.activePlayers',
            'opponent',
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->withCount([
                'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
            ])
            ->when($activeTeamId, fn ($q) => $q->where('team_id', $activeTeamId))
            ->orderBy('scheduled_at');

        $eventsQuery = ClubEvent::with([
            'teams.activePlayers',
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->withCount([
                'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
                'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
                'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
            ])
            ->where('rsvp_enabled', true)
            ->when($activeTeamId, fn ($q) => $q->whereHas('teams', fn ($sq) => $sq->where('teams.id', $activeTeamId)))
            ->orderBy('starts_at');

        // Aplikace filtrů na datum
        if ($filterYear || $filterMonth) {
            if ($filterYear) {
                $trainingsQuery->whereYear('starts_at', $filterYear);
                $matchesQuery->whereYear('scheduled_at', $filterYear);
                $eventsQuery->whereYear('starts_at', $filterYear);
            }
            if ($filterMonth) {
                $trainingsQuery->whereMonth('starts_at', $filterMonth);
                $matchesQuery->whereMonth('scheduled_at', $filterMonth);
                $eventsQuery->whereMonth('starts_at', $filterMonth);
            }
        } else {
            // Defaultně budoucí akce
            $trainingsQuery->where('starts_at', '>=', $now);
            $matchesQuery->where('scheduled_at', '>=', $now);
            $eventsQuery->where('starts_at', '>=', $now);
        }

        // Aplikace filtrů na typ
        if ($filterType !== 'all') {
            if ($filterType === 'training') {
                $matchesQuery->whereRaw('1=0');
                $eventsQuery->whereRaw('1=0');
            } elseif ($filterType === 'match') {
                $trainingsQuery->whereRaw('1=0');
                $eventsQuery->whereRaw('1=0');
            } elseif ($filterType === 'event') {
                $trainingsQuery->whereRaw('1=0');
                $matchesQuery->whereRaw('1=0');
            }
        }

        // Aplikace filtrů na stav docházky
        if ($filterAttendance !== 'all') {
            $applyAttendanceFilter = function ($query, $status, $userId, $dateColumn) {
                if ($status === 'none') {
                    $query->whereDoesntHave('attendances', fn ($q) => $q->where('user_id', $userId));
                } else {
                    $query->whereHas('attendances', fn ($q) => $q->where('user_id', $userId)->where('planned_status', $status));
                }
            };

            $applyAttendanceFilter($trainingsQuery, $filterAttendance, $user->id, 'starts_at');
            $applyAttendanceFilter($matchesQuery, $filterAttendance, $user->id, 'scheduled_at');
            $applyAttendanceFilter($eventsQuery, $filterAttendance, $user->id, 'starts_at');
        }

        $trainings = $trainingsQuery->get()
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

        $matches = $matchesQuery->get()
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

        $events = $eventsQuery->get()
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

        // Seznam roků pro filtr (unikátní roky z dostupných dat)
        $trainingYears = Training::selectRaw('YEAR(starts_at) as year')->distinct()->pluck('year');
        $matchYears = BasketballMatch::selectRaw('YEAR(scheduled_at) as year')->distinct()->pluck('year');
        $eventYears = ClubEvent::selectRaw('YEAR(starts_at) as year')->distinct()->pluck('year');
        $years = $trainingYears->concat($matchYears)->concat($eventYears)->unique()->sort()->values();

        if ($years->isEmpty()) {
            $years->push(now()->year);
        }

        return view('member.attendance.index', [
            'program' => $program,
            'years' => $years,
            'filters' => [
                'type' => $filterType,
                'year' => $filterYear,
                'month' => $filterMonth,
                'attendance' => $filterAttendance,
            ],
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

        // Parametry filtru
        $filterType = $request->get('type', 'all');
        $filterYear = $request->get('year');
        $filterMonth = $request->get('month');
        $filterAttendance = $request->get('attendance', 'all');

        $attendancesQuery = Attendance::with('attendable')
            ->where('user_id', $user->id);

        // Aplikace filtrů na typ
        if ($filterType !== 'all') {
            $modelClass = match ($filterType) {
                'training' => Training::class,
                'match' => BasketballMatch::class,
                'event' => ClubEvent::class,
                default => null,
            };
            if ($modelClass) {
                $attendancesQuery->where('attendable_type', $modelClass);
            }
        }

        // Aplikace filtrů na stav docházky
        if ($filterAttendance !== 'all') {
            if ($filterAttendance === 'none') {
                $attendancesQuery->where(fn ($q) => $q->whereNull('planned_status')->orWhere('planned_status', 'pending'));
            } else {
                $attendancesQuery->where('planned_status', $filterAttendance);
            }
        }

        // Aplikace filtrů na datum (přes attendable událost)
        if ($filterYear || $filterMonth) {
            $attendancesQuery->whereHasMorph('attendable', [Training::class, BasketballMatch::class, ClubEvent::class], function ($query, $type) use ($filterYear, $filterMonth) {
                $dateColumn = ($type === BasketballMatch::class) ? 'scheduled_at' : 'starts_at';
                if ($filterYear) {
                    $query->whereYear($dateColumn, $filterYear);
                }
                if ($filterMonth) {
                    $query->whereMonth($dateColumn, $filterMonth);
                }
            });
        }

        $attendances = $attendancesQuery->orderBy('responded_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Seznam roků pro filtr (unikátní roky z dostupných dat)
        $trainingYears = Training::selectRaw('YEAR(starts_at) as year')->distinct()->pluck('year');
        $matchYears = BasketballMatch::selectRaw('YEAR(scheduled_at) as year')->distinct()->pluck('year');
        $eventYears = ClubEvent::selectRaw('YEAR(starts_at) as year')->distinct()->pluck('year');
        $years = $trainingYears->concat($matchYears)->concat($eventYears)->unique()->sort()->values();

        if ($years->isEmpty()) {
            $years->push(now()->year);
        }

        return view('member.attendance.history', [
            'attendances' => $attendances,
            'years' => $years,
            'filters' => [
                'type' => $filterType,
                'year' => $filterYear,
                'month' => $filterMonth,
                'attendance' => $filterAttendance,
            ],
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

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'attendable_id' => $item->id,
                'attendable_type' => $modelClass,
            ],
            [
                'planned_status' => $request->status,
                'excuse_reason' => $request->status === 'declined' ? $request->excuse_reason : null,
                'note' => $request->note,
                'responded_at' => now(),
            ]
        );

        event(new \App\Events\RsvpChanged($attendance));

        return back()->with('status', __('member.attendance.save_success'));
    }
}
