<?php

namespace App\Filament\Pages;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Training;
use App\Models\UserSeasonConfig;
use App\Support\FilamentIcon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class RealAttendance extends Page
{
    protected static ?string $slug = 'real-attendance';

    protected static string|\BackedEnum|null $navigationIcon = null;

    protected string $view = 'filament.pages.real-attendance';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.real_attendance');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.real_attendance.title');
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public $selectedEventId = null;
    public $selectedEventType = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('super_admin') || $user->can('manage_attendance');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::render(\App\Support\IconHelper::ATTENDANCE);
    }

    public function mount()
    {
        $this->selectedEventId = request()->query('id');
        $this->selectedEventType = request()->query('type');
    }

    public function selectEvent($id, $type)
    {
        $this->selectedEventId = $id;
        $this->selectedEventType = $type;
    }

    public function resetSelection()
    {
        $this->selectedEventId = null;
        $this->selectedEventType = null;
    }

    public function getEventsProperty(): Collection
    {
        $daysRange = 14;
        $startDate = now()->subDays($daysRange)->startOfDay();
        $endDate = now();

        $activeSeason = Season::where('is_active', true)->first();

        $trainings = Training::whereBetween('starts_at', [$startDate, $endDate])
            ->with(['teams.activePlayers', 'attendances'])
            ->get();

        $matches = BasketballMatch::whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['teams.activePlayers', 'attendances', 'opponent', 'team'])
            ->get();

        $clubEvents = ClubEvent::whereBetween('starts_at', [$startDate, $endDate])
            ->with(['teams.activePlayers', 'attendances'])
            ->get();

        // Optimalizace: Získáme všechny uživatele najednou pro výpočet statistik
        $allUserIds = collect([])
            ->concat($trainings)
            ->concat($matches)
            ->concat($clubEvents)
            ->flatMap(function($event) {
                $teams = collect();
                if (method_exists($event, 'teams')) {
                    $teams = $teams->concat($event->teams);
                }
                if ($event instanceof BasketballMatch && $event->team) {
                    if (!$teams->contains('id', $event->team_id)) {
                        $teams->push($event->team);
                    }
                }
                return $teams->flatMap(fn($team) => $team->activePlayers->pluck('user_id'));
            })
            ->unique();

        $configs = $activeSeason
            ? UserSeasonConfig::where('season_id', $activeSeason->id)
                ->whereIn('user_id', $allUserIds)
                ->where('track_attendance', true)
                ->get()
            : collect([]);

        $trainingsMapped = $trainings->map(fn($item) => $this->mapEvent($item, Training::class, __('admin.real_attendance.event_types.training') . ($item->location ? ' - ' . $item->location : ''), $item->starts_at, \App\Support\IconHelper::TRAININGS, $activeSeason, $configs));

        $matchesMapped = $matches->map(fn($item) => $this->mapEvent($item, BasketballMatch::class, __('admin.real_attendance.event_types.match') . ': ' . $item->getOfficialTeamNameAttribute() . ' vs ' . $item->getOfficialOpponentNameAttribute(), $item->scheduled_at, \App\Support\IconHelper::MATCHES, $activeSeason, $configs));

        $clubEventsMapped = $clubEvents->map(fn($item) => $this->mapEvent($item, ClubEvent::class, $item->getTranslation('title', app()->getLocale()), $item->starts_at, \App\Support\IconHelper::EVENTS, $activeSeason, $configs));

        return collect([])
            ->concat($trainingsMapped)
            ->concat($matchesMapped)
            ->concat($clubEventsMapped)
            ->sortByDesc('starts_at');
    }

    protected function mapEvent($item, $type, $title, $startsAt, $icon, $activeSeason, $configs = null)
    {
        $stats = $this->calculateStats($item, $activeSeason, $configs);

        return [
            'id' => $item->id,
            'type' => $type,
            'title' => $title,
            'starts_at' => $startsAt,
            'icon' => \App\Support\IconHelper::render($icon),
            'teams' => $item->teams->pluck('name')->toArray(),
            'stats' => $stats,
        ];
    }

    protected function calculateStats($event, $activeSeason, $configs = null)
    {
        if (!$activeSeason) {
            return [
                'expected' => 0,
                'attended' => 0,
                'excused' => 0,
            ];
        }

        // Získáme všechny unikátní uživatele (hráče) z týmů události
        $teams = collect();
        if (method_exists($event, 'teams')) {
            $teams = $teams->concat($event->teams);
        }
        if ($event instanceof BasketballMatch && $event->team) {
            if (!$teams->contains('id', $event->team_id)) {
                $teams->push($event->team);
            }
        }

        $userIds = $teams->flatMap(function($team) {
            return $team->activePlayers->pluck('user_id');
        })->unique();

        if ($userIds->isEmpty()) {
            return [
                'expected' => 0,
                'attended' => $event->attendances->where('actual_status', 'attended')->count(),
                'excused' => $event->attendances->where('planned_status', 'declined')->count(),
            ];
        }

        // Zjistíme, kolik z nich má zapnutý track_attendance v této sezóně
        if ($configs) {
            $expectedCount = $configs->whereIn('user_id', $userIds)->count();
        } else {
            $expectedCount = UserSeasonConfig::where('season_id', $activeSeason->id)
                ->whereIn('user_id', $userIds)
                ->where('track_attendance', true)
                ->count();
        }

        return [
            'expected' => $expectedCount,
            'attended' => $event->attendances->where('actual_status', 'attended')->count(),
            'excused' => $event->attendances->where('planned_status', 'declined')->count(),
        ];
    }

    public function getSelectedEventProperty()
    {
        if (!$this->selectedEventId || !$this->selectedEventType) {
            return null;
        }

        $query = $this->selectedEventType::query();

        if ($this->selectedEventType === BasketballMatch::class) {
            $query->with(['team', 'opponent', 'venue', 'teams.activePlayers']);
        } elseif ($this->selectedEventType === Training::class || $this->selectedEventType === ClubEvent::class) {
            $query->with(['teams.activePlayers']);
        }

        return $query->find($this->selectedEventId);
    }
}
