<?php

namespace App\Livewire\Admin;

use App\Filament\Pages\RealAttendance;
use App\Services\Attendance\RealAttendanceService;
use App\Services\Finance\FinanceAutomationService;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AttendanceTracker extends Component
{
    #[Locked]
    public $attendableId;

    #[Locked]
    public $attendableType;

    public string $search = '';

    protected $listeners = ['refreshAttendance' => '$refresh'];

    public function boot(): void
    {
        // Nested Livewire requests must enforce the page permission independently.
        abort_unless(RealAttendance::canAccess(), 403);
    }

    public function mount($attendableId, $attendableType): void
    {
        $this->attendableId = $attendableId;
        $this->attendableType = $attendableType;
        $this->getAttendableProperty();
    }

    public function getAttendableProperty(): Model
    {
        abort_unless(is_string($this->attendableType) && RealAttendanceService::supportsType($this->attendableType), 404);

        return $this->attendableType::findOrFail($this->attendableId);
    }

    public function getParticipantsProperty()
    {
        return app(RealAttendanceService::class)->participants($this->attendable);
    }

    public function getAttendancesProperty()
    {
        return $this->attendable->attendances()->where('actual_status', 'attended')->with('user')->get();
    }

    public function getUsersProperty()
    {
        if (mb_strlen(trim($this->search)) < 2) {
            return collect();
        }

        return app(RealAttendanceService::class)->searchablePlayers()
            ->where('name', 'like', '%'.mb_substr(trim($this->search), 0, 100).'%')
            ->whereNotIn('id', $this->participants->pluck('user.id'))
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function selectUser(int $userId): void
    {
        $this->setPresent($userId, true);
        $this->search = '';
    }

    public function setPresent(int $userId, bool $present): void
    {
        abort_unless(RealAttendance::canAccess(), 403);
        app(RealAttendanceService::class)->setPresent($this->attendable, $userId, $present);
        $this->refreshRows();
    }

    public function markConfirmedPresent(): void
    {
        abort_unless(RealAttendance::canAccess(), 403);
        app(RealAttendanceService::class)->markConfirmedPresent($this->attendable);
        $this->refreshRows();
    }

    public function removeAttendance(int $attendanceId): void
    {
        abort_unless(RealAttendance::canAccess(), 403);
        $attendance = $this->attendable->attendances()->find($attendanceId);
        abort_unless($attendance, 404);
        $attendance->update(['actual_status' => null]);
        $this->refreshRows();
    }

    protected function refreshRows(): void
    {
        unset($this->participants, $this->attendances, $this->users);
        $this->dispatch('refreshAttendance');
    }

    public function finalize(): void
    {
        abort_unless(RealAttendance::canAccess(), 403);
        app(FinanceAutomationService::class)->finalizeAttendance($this->attendable);
        $this->refreshRows();
        session()->flash('message', __('admin.real_attendance.tracker.finalized'));
    }

    public function render()
    {
        return view('livewire.admin.attendance-tracker');
    }
}
