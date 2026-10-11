<?php

namespace App\Services;

use App\Models\Opponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpponentQuickCreateService
{
    public const CREATE_OPTION_PREFIX = '__create_opponent__:';

    /**
     * @return array<int|string, string>
     */
    public function searchOptions(string $search, int $limit = 50): array
    {
        $name = $this->normalizeName($search);

        if ($name === '') {
            return [];
        }

        $needle = $this->comparisonKey($name);
        $matches = Opponent::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->filter(fn (Opponent $opponent): bool => str_contains(
                $this->comparisonKey($opponent->name),
                $needle,
            ));

        $exactMatch = $matches->first(
            fn (Opponent $opponent): bool => $this->comparisonKey($opponent->name) === $needle,
        );

        if ($exactMatch) {
            $matches = $matches->prepend($exactMatch)->unique('id');
        }

        $options = $matches
            ->take($limit)
            ->mapWithKeys(fn (Opponent $opponent): array => [$opponent->getKey() => $opponent->name])
            ->all();

        if (! $exactMatch && mb_strlen($name) <= 255) {
            $options[$this->createOptionValue($name)] = __(
                'admin.resources.basketball_match.actions.create_opponent',
                ['name' => $name],
            );
        }

        return $options;
    }

    public function isCreateOption(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, self::CREATE_OPTION_PREFIX);
    }

    public function createOptionValue(string $name): string
    {
        $encodedName = rtrim(strtr(base64_encode($this->normalizeName($name)), '+/', '-_'), '=');

        return self::CREATE_OPTION_PREFIX.$encodedName;
    }

    public function createFromOption(string $value): Opponent
    {
        $name = $this->nameFromCreateOption($value);

        if ($name === null) {
            throw ValidationException::withMessages([
                'opponent_id' => __('admin.resources.basketball_match.validation.invalid_opponent_option'),
            ]);
        }

        return $this->findOrCreate($name);
    }

    public function findOrCreate(string $name): Opponent
    {
        $name = $this->normalizeName($name);

        validator(
            ['name' => $name],
            ['name' => ['required', 'string', 'max:255']],
        )->validate();

        $comparisonKey = $this->comparisonKey($name);

        return DB::transaction(function () use ($comparisonKey, $name): Opponent {
            $existing = Opponent::query()
                ->lockForUpdate()
                ->get()
                ->first(fn (Opponent $opponent): bool => $this->comparisonKey($opponent->name) === $comparisonKey);

            return $existing ?? Opponent::create(['name' => $name]);
        });
    }

    public function normalizeName(string $name): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $name));
    }

    private function comparisonKey(string $name): string
    {
        return Str::lower($this->normalizeName($name));
    }

    private function nameFromCreateOption(string $value): ?string
    {
        if (! $this->isCreateOption($value)) {
            return null;
        }

        $encodedName = substr($value, strlen(self::CREATE_OPTION_PREFIX));

        if ($encodedName === '' || preg_match('/^[A-Za-z0-9_-]+$/', $encodedName) !== 1) {
            return null;
        }

        $padding = (4 - (strlen($encodedName) % 4)) % 4;
        $decodedName = base64_decode(strtr($encodedName.str_repeat('=', $padding), '-_', '+/'), true);

        if ($decodedName === false || ! mb_check_encoding($decodedName, 'UTF-8')) {
            return null;
        }

        $name = $this->normalizeName($decodedName);

        if ($name === '' || $this->createOptionValue($name) !== $value) {
            return null;
        }

        return $name;
    }
}
