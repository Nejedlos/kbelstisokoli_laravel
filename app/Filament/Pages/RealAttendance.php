<?php

namespace App\Filament\Pages;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
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
        return 'Reálná docházka';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Reálná docházka';
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

        $trainings = Training::whereBetween('starts_at', [$startDate, $endDate])
            ->with('teams')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => Training::class,
                'title' => 'Trénink ' . ($item->location ? ' - ' . $item->location : ''),
                'starts_at' => $item->starts_at,
                'icon' => \App\Support\IconHelper::TRAININGS,
                'teams' => $item->teams->pluck('name')->toArray(),
            ]);

        $matches = BasketballMatch::whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['teams', 'opponent'])
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => BasketballMatch::class,
                'title' => 'Zápas: ' . $item->getOfficialTeamNameAttribute() . ' vs ' . $item->getOfficialOpponentNameAttribute(),
                'starts_at' => $item->scheduled_at,
                'icon' => \App\Support\IconHelper::MATCHES,
                'teams' => $item->teams->pluck('name')->toArray(),
            ]);

        $clubEvents = ClubEvent::whereBetween('starts_at', [$startDate, $endDate])
            ->with('teams')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => ClubEvent::class,
                'title' => $item->getTranslation('title', 'cs'),
                'starts_at' => $item->starts_at,
                'icon' => \App\Support\IconHelper::EVENTS,
                'teams' => $item->teams->pluck('name')->toArray(),
            ]);

        return collect([])
            ->concat($trainings)
            ->concat($matches)
            ->concat($clubEvents)
            ->sortByDesc('starts_at');
    }

    public function getSelectedEventProperty()
    {
        if (!$this->selectedEventId || !$this->selectedEventType) {
            return null;
        }

        return $this->selectedEventType::find($this->selectedEventId);
    }
}
