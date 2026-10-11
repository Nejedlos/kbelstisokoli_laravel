<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RealAttendanceService
{
    public static function supportsType(string $type): bool
    {
        return in_array($type, [Training::class, BasketballMatch::class, ClubEvent::class], true);
    }

    public function participants(Model $event): Collection
    {
        // Show positive RSVPs and people already added as actually present.
        // Other team members remain available through the player search.
        $attendances = $event->attendances()
            ->where(fn (Builder $query) => $query->where('planned_status', 'confirmed')->orWhere('actual_status', 'attended'))
            ->get()->keyBy('user_id');
        $userIds = $attendances->keys();

        return User::whereIn('id', $userIds)->orderBy('name')->get()
            ->map(fn (User $user) => [
                'user' => $user,
                'planned_status' => $attendances->get($user->id)?->planned_status ?? 'pending',
                'actual_status' => $attendances->get($user->id)?->actual_status,
            ])
            ->sortBy(fn (array $row) => $row['planned_status'] === 'confirmed' ? 0 : 1)
            ->values();
    }

    public function searchablePlayers(): Builder
    {
        return User::query()->whereHas('playerProfiles', fn (Builder $query) => $query->where('is_active', true));
    }

    public function setPresent(Model $event, int $userId, bool $present): void
    {
        $attendance = $event->attendances()->where('user_id', $userId)->first();
        abort_unless($attendance || $this->searchablePlayers()->whereKey($userId)->exists(), 404);

        if ($present) {
            // Keep planned_status, response time and excuses exactly as the player entered them.
            $event->attendances()->updateOrCreate(['user_id' => $userId], ['actual_status' => 'attended']);
        } elseif ($attendance) {
            $attendance->update(['actual_status' => null]);
        }
    }

    public function markConfirmedPresent(Model $event): void
    {
        DB::transaction(function () use ($event) {
            $event->attendances()->where('planned_status', 'confirmed')->lockForUpdate()->get()
                ->each(fn (Attendance $attendance) => $attendance->update(['actual_status' => 'attended']));
        });
    }
}
