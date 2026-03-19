<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class AttendanceTracker extends Component
{
    public $attendableId;
    public $attendableType;
    public $search = '';
    public $selectedUserId = null;

    protected $listeners = ['refreshAttendance' => '$refresh'];

    public function mount($attendableId, $attendableType)
    {
        $this->attendableId = $attendableId;
        $this->attendableType = $attendableType;
    }

    public function getAttendableProperty(): ?Model
    {
        if (!$this->attendableType || !$this->attendableId) {
            return null;
        }

        try {
            if (!class_exists($this->attendableType)) {
                return null;
            }
            return $this->attendableType::with(['teams'])->find($this->attendableId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getAttendancesProperty()
    {
        return Attendance::where('attendable_id', $this->attendableId)
            ->where('attendable_type', $this->attendableType)
            ->where('actual_status', 'attended')
            ->with('user')
            ->latest('responded_at')
            ->get();
    }

    public function getUsersProperty()
    {
        if (strlen($this->search) < 2) {
            return [];
        }

        $attendable = $this->attendable;
        $teamIds = [];

        if ($attendable && $attendable->relationLoaded('teams')) {
            $teamIds = $attendable->teams->pluck('id')->toArray();
        } elseif ($attendable && method_exists($attendable, 'teams')) {
            $teamIds = $attendable->teams()->pluck('teams.id')->toArray();
        }

        $query = User::query();

        if (!empty($teamIds)) {
            $query->where(function($q) use ($teamIds) {
                $q->whereHas('teams', fn($t) => $t->whereIn('teams.id', $teamIds))
                  ->orWhereHas('playerProfiles.teams', fn($t) => $t->whereIn('teams.id', $teamIds));
            });
        }

        return $query->where('name', 'like', '%' . $this->search . '%')
            ->whereNotIn('id', $this->attendances->pluck('user_id'))
            ->limit(10)
            ->get();
    }

    public function selectUser($userId)
    {
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $userId,
                'attendable_id' => $this->attendableId,
                'attendable_type' => $this->attendableType,
            ],
            [
                'actual_status' => 'attended',
                'responded_at' => now(),
            ]
        );

        $this->search = '';
        $this->dispatch('refreshAttendance');
    }

    public function removeAttendance($attendanceId)
    {
        $attendance = Attendance::find($attendanceId);
        if ($attendance) {
            $attendance->update(['actual_status' => null]);
        }
        $this->dispatch('refreshAttendance');
    }

    public function finalize()
    {
        $event = $this->attendable;
        if ($event) {
            app(\App\Services\Finance\FinanceAutomationService::class)->finalizeAttendance($event);
            session()->flash('message', 'Docházka uzavřena a pokuty vygenerovány.');
        }
    }

    public function render()
    {
        return view('livewire.admin.attendance-tracker');
    }
}
