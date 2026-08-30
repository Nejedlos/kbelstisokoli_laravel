<?php

namespace App\Services\Attendance;

use App\Jobs\SendAttendanceEmailJob;
use App\Models\AttendanceEmailDelivery;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSeasonConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceEmailService
{
    public const TYPE_MAP = [
        'training' => Training::class,
        'match' => BasketballMatch::class,
        'event' => ClubEvent::class,
    ];

    public function dispatchDue(Carbon $now): array
    {
        $counts = ['reminders' => 0, 'summaries' => 0];

        foreach ($this->upcomingEvents($now) as $event) {
            $stage = $this->dueReminderStage($event->starts_at, $now);

            if ($stage) {
                foreach ($this->players($event) as $user) {
                    if (! $this->hasResponse($event, $user) && $user->prefersNotification('attendance_reminders')) {
                        $counts['reminders'] += $this->dispatch($event, $user, 'reminder', $stage);
                    }
                }
            }

            if ($this->summaryIsDue($event->starts_at, $now)) {
                foreach ($this->summaryRecipients($event) as $recipient) {
                    if ($recipient->prefersNotification('attendance_summaries')) {
                        $counts['summaries'] += $this->dispatch($event, $recipient, 'summary', 'day_of');
                    }
                }
            }
        }

        return $counts;
    }

    public function upcomingEvents(Carbon $now): Collection
    {
        $until = $now->copy()->addDays(8)->endOfDay();
        $from = $now->copy()->subDay()->startOfDay();

        $trainings = Training::with('teams')->whereBetween('starts_at', [$from, $until])->get();
        $matches = BasketballMatch::with(['team', 'teams', 'opponent'])->whereBetween('scheduled_at', [$from, $until])
            ->whereIn('status', ['planned', 'scheduled'])->get();
        $events = ClubEvent::with('teams')->whereBetween('starts_at', [$from, $until])->where('rsvp_enabled', true)->get();

        return $trainings->concat($matches)->concat($events)
            ->each(fn (Model $event) => $event->setAttribute('starts_at', $this->startsAt($event)))
            ->filter(fn (Model $event) => $event->starts_at->gt($now->copy()->addMinutes(90)));
    }

    public function players(Model $event): Collection
    {
        $seasonId = $event instanceof BasketballMatch
            ? $event->season_id
            : Season::where('is_active', true)->value('id');
        $teams = $this->teams($event);
        $users = $teams->flatMap(fn ($team) => $team->activePlayers()->with('user')->get()->pluck('user'))
            ->filter(fn (?User $user) => $user?->is_active && ! $user->isGhost())
            ->unique('id')->values();

        if (! $seasonId || $users->isEmpty()) {
            return collect();
        }

        $tracked = UserSeasonConfig::where('season_id', $seasonId)->where('track_attendance', true)
            ->whereIn('user_id', $users->pluck('id'))->pluck('user_id')->flip();

        return $users->filter(fn (User $user) => $tracked->has($user->id))->values();
    }

    public function coaches(Model $event): Collection
    {
        return $this->teams($event)->flatMap(fn ($team) => $team->activeCoaches()->get())
            ->filter(fn (User $user) => ! $user->isGhost())->unique('id')->values();
    }

    public function summaryRecipients(Model $event): Collection
    {
        return $this->summaryRoster($event)->concat($this->coaches($event))->unique('id')->values();
    }

    public function summaryRoster(Model $event): Collection
    {
        return $this->teams($event)
            ->flatMap(fn ($team) => $team->activePlayers()->with('user')->get()->pluck('user'))
            ->filter(fn (?User $user) => $user?->is_active && ! $user->isGhost())
            ->unique('id')->values();
    }

    public function teams(Model $event): Collection
    {
        if (! $event->relationLoaded('teams')) {
            $event->load('teams');
        }
        if ($event instanceof BasketballMatch && ! $event->relationLoaded('team')) {
            $event->load('team');
        }

        $teams = collect($event->teams);
        if ($event instanceof BasketballMatch && $event->team && ! $teams->contains('id', $event->team->id)) {
            $teams->push($event->team);
        }

        return $teams->filter()->unique('id')->values();
    }

    public function hasResponse(Model $event, User $user): bool
    {
        return $event->attendances()->where('user_id', $user->id)
            ->whereIn('planned_status', ['confirmed', 'declined', 'maybe'])->exists();
    }

    public function typeFor(Model $event): string
    {
        return array_search($event::class, self::TYPE_MAP, true) ?: 'event';
    }

    public function startsAt(Model $event): Carbon
    {
        return $event instanceof BasketballMatch ? $event->scheduled_at : $event->starts_at;
    }

    private function dueReminderStage(Carbon $startsAt, Carbon $now): ?string
    {
        $daySend = $startsAt->copy()->startOfDay()->addHours(7);
        if ($startsAt->hour < 10) {
            $daySend = $startsAt->copy()->subDay()->setTime(19, 0);
        }

        $windows = [
            'week' => [$startsAt->copy()->subDays(7)->setTime(8, 0), $startsAt->copy()->subDays(3)->setTime(8, 0)],
            'three_days' => [$startsAt->copy()->subDays(3)->setTime(8, 0), $daySend],
            'day_of' => [$daySend, $startsAt->copy()->subMinutes(90)],
        ];

        foreach ($windows as $stage => [$from, $until]) {
            if ($now->betweenIncluded($from, $until)) {
                return $stage;
            }
        }

        return null;
    }

    private function summaryIsDue(Carbon $startsAt, Carbon $now): bool
    {
        $sendAt = $startsAt->copy()->startOfDay()->addHours(7)->addMinutes(15);
        if ($startsAt->lt($sendAt->copy()->addHours(3))) {
            $sendAt = $startsAt->copy()->subHours(3);
        }

        return $now->betweenIncluded($sendAt, $startsAt);
    }

    private function dispatch(Model $event, User $user, string $kind, string $stage): int
    {
        $delivery = AttendanceEmailDelivery::firstOrCreate([
            'user_id' => $user->id,
            'attendable_id' => $event->id,
            'attendable_type' => $event::class,
            'kind' => $kind,
            'stage' => $stage,
        ]);

        if (! $delivery->wasRecentlyCreated) {
            return 0;
        }

        SendAttendanceEmailJob::dispatch($delivery->id)->onQueue('critical-mail');

        return 1;
    }
}
