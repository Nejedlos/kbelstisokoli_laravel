<?php

namespace App\Services\Matches;

use App\Exceptions\DuplicateMatchException;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\OpponentMergeSuggestion;
use App\Models\Team;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Shared serialization boundary for manual and imported fixture writes. */
class MatchWriteService
{
    private array $lockedTeamIds = [];

    public function withTeamLocks(array $teamIds, Closure $callback): mixed
    {
        $teamIds = $this->normalizeTeamIds($teamIds);

        return DB::transaction(function () use ($teamIds, $callback) {
            // All writers take the same existing rows, including when no fixture exists yet.
            $locked = Team::whereIn('id', $teamIds)->orderBy('id')->lockForUpdate()->pluck('id')->all();
            if (count($locked) !== count($teamIds)) {
                throw ValidationException::withMessages(['teams' => __('matches.validation.invalid_teams')]);
            }

            $previous = $this->lockedTeamIds;
            $this->lockedTeamIds = array_values(array_unique([...$previous, ...$teamIds]));
            try {
                return $callback();
            } finally {
                $this->lockedTeamIds = $previous;
            }
        }, 3);
    }

    public function withImportTeamLocks(int $teamId, Closure $callback): mixed
    {
        // A shared fixture can affect every attached team. Discover its lock set,
        // then verify it again on the current locked record before any mutation.
        $teamIds = [$teamId];
        $matches = BasketballMatch::where('team_id', $teamId)
            ->orWhereHas('teams', fn ($teams) => $teams->where('teams.id', $teamId))->get();
        foreach ($matches as $match) {
            $teamIds = [...$teamIds, ...$this->teamIds($match)];
        }

        return $this->withTeamLocks(array_values(array_unique($teamIds)), $callback);
    }

    public function saveManual(array $data, array $teamIds, ?BasketballMatch $record = null): BasketballMatch
    {
        $teamIds = $this->normalizeTeamIds($teamIds);
        $oldTeamIds = $record ? $this->teamIds($record->fresh()) : [];
        $legacyId = array_key_exists('team_id', $data) ? $data['team_id'] : $record?->team_id;
        $newTeamIds = array_values(array_unique([...$teamIds, ...($legacyId ? [(int) $legacyId] : [])]));
        $lockIds = array_values(array_unique([...$oldTeamIds, ...$newTeamIds]));

        return $this->withTeamLocks($lockIds, function () use ($data, $teamIds, $lockIds, $record) {
            $fresh = $record ? BasketballMatch::whereKey($record->id)->lockForUpdate()->firstOrFail() : new BasketballMatch;
            // A concurrent edit may have moved the fixture before we acquired its former team locks.
            if ($fresh->exists && array_diff($this->teamIds($fresh, true), $lockIds)) {
                throw ValidationException::withMessages(['scheduled_at' => __('matches.validation.stale_edit')]);
            }
            unset($data['teams']);
            $fresh->fill($data);
            if ($fresh->team_id && ! in_array((int) $fresh->team_id, $teamIds, true)) {
                $fresh->team_id = null;
            }
            $newTeamIds = array_values(array_unique([...$teamIds, ...($fresh->team_id ? [(int) $fresh->team_id] : [])]));
            foreach ($this->candidates($newTeamIds, $fresh->season_id) as $candidate) {
                if ($candidate->id === $fresh->id) {
                    continue;
                }
                if ($this->sameExternalIdentity($fresh, $candidate) || $this->sameFixture($candidate, $fresh->scheduled_at, $fresh->is_home, $fresh->opponent_id)) {
                    throw new DuplicateMatchException($candidate->id);
                }
            }
            $fresh->save();
            $fresh->teams()->sync($teamIds);

            return $fresh->refresh();
        });
    }

    /** Advisory only: a different time is not sufficient evidence to block a fixture. */
    public function findSimilar(array $data, array $teamIds, ?int $excludeId = null): Collection
    {
        if (empty($data['scheduled_at']) || empty($data['season_id']) || empty($data['opponent_id']) || $teamIds === []) {
            return new Collection;
        }
        try {
            $date = Carbon::parse($data['scheduled_at']);
            $teamIds = $this->normalizeTeamIds($teamIds);
        } catch (\Exception) {
            return new Collection;
        }

        return BasketballMatch::where('season_id', $data['season_id'])
            ->where(fn ($query) => $query->whereIn('team_id', $teamIds)->orWhereHas('teams', fn ($teams) => $teams->whereIn('teams.id', $teamIds)))
            ->whereDate('scheduled_at', $date->toDateString())
            ->where('is_home', (bool) ($data['is_home'] ?? true))
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->orderBy('scheduled_at')->get()
            ->filter(fn ($match) => ! $match->scheduled_at->equalTo($date) && $this->sameOpponent($match, (int) $data['opponent_id']))
            ->take(5)->values();
    }

    /** Call only inside withTeamLocks, immediately before writing the returned fixture. */
    public function resolveImported(int $teamId, int $seasonId, ?string $externalId, Carbon $date, bool $isHome, int $opponentId, string $source = 'czbasketball'): ?BasketballMatch
    {
        if (! in_array($teamId, $this->lockedTeamIds, true)) {
            throw new \LogicException('Imported match resolution requires the shared team lock.');
        }
        $candidates = $this->candidates([$teamId], $seasonId);
        $externalId = trim($externalId ?? '');
        $exact = $externalId === '' ? collect() : $candidates->filter(fn ($match) => $this->source($match) === $source && in_array($externalId, $this->externalIds($match), true));
        if ($exact->count() > 1) {
            throw new DuplicateMatchException($exact->first()->id);
        }
        $existing = $exact->first();
        $fixtures = $candidates->filter(fn ($match) => $match->id !== $existing?->id && $this->sameFixture($match, $date, $isHome, $opponentId));
        if ($existing) {
            // Moving an identified game must not overwrite or strand another game at the destination.
            if ($fixtures->isNotEmpty() || count($this->externalIds($existing)) > 1) {
                throw new DuplicateMatchException($fixtures->first()?->id ?? $existing->id);
            }

            $this->assertLockedMembership($existing);
            $this->assertImportedDestination($existing, $date, $isHome, $opponentId);

            return $existing;
        }
        if ($fixtures->count() > 1) {
            throw new DuplicateMatchException($fixtures->first()->id);
        }
        $legacy = $fixtures->first();
        if ($legacy && $externalId !== '' && $this->externalIds($legacy) !== []) {
            throw new DuplicateMatchException($legacy->id);
        }

        if ($legacy) {
            $this->assertLockedMembership($legacy);
            $this->assertImportedDestination($legacy, $date, $isHome, $opponentId);
        }

        return $legacy;
    }

    private function assertLockedMembership(BasketballMatch $match): void
    {
        if (array_diff($this->teamIds($match, true), $this->lockedTeamIds)) {
            throw ValidationException::withMessages(['scheduled_at' => __('matches.validation.stale_edit')]);
        }
    }

    public function lockExistingForUpdate(BasketballMatch $record, int $teamId, ?Carbon $scheduledAt = null): BasketballMatch
    {
        if (! in_array($teamId, $this->lockedTeamIds, true)) {
            throw new \LogicException('Match update requires the shared team lock.');
        }
        $match = BasketballMatch::whereKey($record->id)->lockForUpdate()->firstOrFail();
        $this->assertLockedMembership($match);
        if (! in_array($teamId, $this->teamIds($match, true), true)) {
            throw ValidationException::withMessages(['scheduled_at' => __('matches.validation.stale_edit')]);
        }
        $date = $scheduledAt ?? $match->scheduled_at;
        if ($date && $match->opponent_id) {
            $this->assertImportedDestination($match, $date, $match->is_home, $match->opponent_id);
        }

        return $match;
    }

    private function assertImportedDestination(BasketballMatch $match, Carbon $date, bool $isHome, int $opponentId): void
    {
        foreach ($this->candidates($this->teamIds($match, true), $match->season_id) as $candidate) {
            if ($candidate->id !== $match->id && ($this->sameExternalIdentity($match, $candidate) || $this->sameFixture($candidate, $date, $isHome, $opponentId))) {
                throw new DuplicateMatchException($candidate->id);
            }
        }
    }

    private function normalizeTeamIds(array $teamIds): array
    {
        foreach ($teamIds as $id) {
            if (! is_scalar($id) || ! ctype_digit((string) $id) || (int) $id < 1) {
                throw ValidationException::withMessages(['teams' => __('matches.validation.invalid_teams')]);
            }
        }
        $ids = array_values(array_unique(array_map('intval', $teamIds)));
        sort($ids, SORT_NUMERIC);
        if ($ids === []) {
            throw ValidationException::withMessages(['teams' => __('matches.validation.invalid_teams')]);
        }

        return $ids;
    }

    private function teamIds(BasketballMatch $match, bool $lock = false): array
    {
        $query = DB::table('basketball_match_team')->where('basketball_match_id', $match->id);
        if ($lock) {
            $query->lockForUpdate();
        }

        return array_values(array_unique([...$query->pluck('team_id')->map(fn ($id) => (int) $id)->all(), ...($match->team_id ? [(int) $match->team_id] : [])]));
    }

    private function candidates(array $teamIds, ?int $seasonId): Collection
    {
        // Locking reads deliberately avoid a pre-lock REPEATABLE READ snapshot on MySQL.
        $pivotMatchIds = DB::table('basketball_match_team')->whereIn('team_id', $teamIds)
            ->orderBy('team_id')->orderBy('basketball_match_id')->lockForUpdate()->pluck('basketball_match_id');

        return BasketballMatch::where('season_id', $seasonId)
            ->where(fn ($query) => $query->whereIn('team_id', $teamIds)->orWhereIn('id', $pivotMatchIds))
            ->orderBy('id')->lockForUpdate()->get();
    }

    private function sameFixture(BasketballMatch $candidate, ?Carbon $date, ?bool $isHome, ?int $opponentId): bool
    {
        if (! $date || ! $candidate->scheduled_at || ! $candidate->scheduled_at->equalTo($date) || $candidate->is_home !== $isHome || ! $opponentId || ! $candidate->opponent_id) {
            return false;
        }

        return $this->sameOpponent($candidate, $opponentId);
    }

    private function sameOpponent(BasketballMatch $candidate, int $opponentId): bool
    {
        if ($candidate->opponent_id === $opponentId) {
            return true;
        }
        $left = $this->normalizeOpponentAlias($candidate->opponent?->name ?? '');
        $right = $this->normalizeOpponentAlias(Opponent::find($opponentId)?->name ?? '');
        if ($left === '' || $left !== $right) {
            return false;
        }

        return ! OpponentMergeSuggestion::where('status', 'rejected')
            ->where(fn ($query) => $query
                ->where(fn ($pair) => $pair->where('source_opponent_id', $opponentId)->where('target_opponent_id', $candidate->opponent_id))
                ->orWhere(fn ($pair) => $pair->where('source_opponent_id', $candidate->opponent_id)->where('target_opponent_id', $opponentId)))
            ->exists();
    }

    private function normalizeOpponentAlias(string $name): string
    {
        $name = mb_strtolower(Str::ascii(trim($name)));
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);
        $name = preg_replace('/^(tj|sk|bk|bc)\s+/', '', trim($name));
        $name = preg_replace('/\bpraha\b/', '', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function externalIds(BasketballMatch $match): array
    {
        $ids = array_map(fn ($key) => trim((string) ($match->metadata[$key] ?? '')), ['external_id', 'season_external_match_id', 'external_match_id']);

        return array_values(array_unique(array_filter($ids, fn ($id) => $id !== '')));
    }

    private function source(BasketballMatch $match): string
    {
        return (string) ($match->metadata['source'] ?? $match->metadata['source_key'] ?? 'czbasketball');
    }

    private function sameExternalIdentity(BasketballMatch $left, BasketballMatch $right): bool
    {
        return $this->source($left) === $this->source($right) && array_intersect($this->externalIds($left), $this->externalIds($right)) !== [];
    }
}
