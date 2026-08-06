<?php

namespace App\Livewire\Member;

use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use Livewire\Attributes\On;
use Livewire\Component;

class EventCard extends Component
{
    public $type;
    public $eventId;
    public $showActions = true;
    public $compact = false;

    // Pro Alpine.js integraci
    public $selectedEvents = [];

    public function mount($event, $showActions = true, $compact = false)
    {
        $this->type = $event['type'];
        $this->eventId = $event['data']->id;
        $this->showActions = $showActions;
        $this->compact = $compact;
    }

    #[On('attendanceUpdated')]
    public function refresh($eventId = null, $type = null)
    {
        if ($eventId === null || ($eventId == $this->eventId && $type == $this->type)) {
            // Livewire automaticky re-renderuje při zavolání metody
        }
    }

    public function setStatus($status)
    {
        $modelClass = match ($this->type) {
            'training' => Training::class,
            'match' => BasketballMatch::class,
            'event' => ClubEvent::class,
            default => null,
        };

        if (!$modelClass) return;

        $item = $modelClass::findOrFail($this->eventId);

        $eventDate = match ($this->type) {
            'match' => $item->scheduled_at,
            default => $item->starts_at,
        };

        if ($eventDate->isBefore(now()->addMinutes(90))) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('member.attendance.deadline_reached')
            ]);
            return;
        }

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'attendable_id' => $item->id,
                'attendable_type' => $modelClass,
            ],
            [
                'planned_status' => $status,
                'responded_at' => now(),
            ]
        );

        event(new \App\Events\RsvpChanged($attendance));

        // Dispatch event pro ostatní komponenty (pokud by někdo poslouchal)
        $this->dispatch('attendanceUpdated', eventId: $this->eventId, type: $this->type);
        
        // Vizuální feedback (volitelně můžeme použít flash, ale u AJAXu je lepší tichá aktualizace nebo toast)
        // $this->dispatch('notify', ['type' => 'success', 'message' => __('member.attendance.save_success')]);
    }

    public function render()
    {
        $user = auth()->user();
        
        $modelClass = match ($this->type) {
            'training' => Training::class,
            'match' => BasketballMatch::class,
            'event' => ClubEvent::class,
            default => abort(404),
        };

        $query = $modelClass::with([
            'attendances' => fn ($q) => $q->where('user_id', $user->id),
        ])->withCount([
            'attendances as confirmed_count' => fn ($q) => $q->where('planned_status', 'confirmed'),
            'attendances as declined_count' => fn ($q) => $q->where('planned_status', 'declined'),
            'attendances as maybe_count' => fn ($q) => $q->where('planned_status', 'maybe'),
        ]);

        if ($this->type === 'match') {
            $query->with(['team', 'opponent', 'prediction']);
        } else {
            $query->with(['teams.activePlayers:player_profiles.id,user_id']);
        }

        $data = $query->findOrFail($this->eventId);

        // Výpočet očekávaných hráčů (převzato z AttendanceController)
        $currentSeasonId = \App\Models\Season::where('is_active', true)->first()?->id;
        $trackedUserIds = $currentSeasonId
            ? \Illuminate\Support\Facades\Cache::remember("tracked_user_ids_{$currentSeasonId}", 3600, function () use ($currentSeasonId) {
                return \App\Models\UserSeasonConfig::where('season_id', $currentSeasonId)
                    ->where('track_attendance', true)
                    ->pluck('user_id')
                    ->toArray();
            })
            : [];

        $expectedIds = collect();
        $teams = $this->type === 'match' ? collect([$data->team])->concat($data->teams)->filter()->unique('id') : $data->teams;

        foreach ($teams as $team) {
            $expectedIds = $expectedIds->concat(
                $team->activePlayers
                    ->pluck('user_id')
                    ->filter(fn($uid) => in_array($uid, $trackedUserIds))
            );
        }
        $data->expected_players_count = $expectedIds->unique()->count();
        
        // Znovu sestavíme event pole pro šablonu (aby byla kompatibilní s původní)
        $event = [
            'type' => $this->type,
            'data' => $data,
            'time' => match ($this->type) {
                'match' => $data->scheduled_at,
                default => $data->starts_at,
            },
        ];

        return view('livewire.member.event-card', [
            'event' => $event,
        ]);
    }
}
