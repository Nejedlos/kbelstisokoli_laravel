<?php

namespace App\Services\Stats\Sync;

use App\Models\ExternalEntityMapping;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\PlayerProfile;
use App\Models\User;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RosterSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected TeamRosterExtractor $extractor
    ) {}

    /**
     * Synchronizuje soupisku pro daný tým a sezónu (stáhne data z webu).
     */
    public function sync(ExternalTeamSeasonConfig $config, ?int $userId = null): ExternalImportRun
    {
        $run = ExternalImportRun::start($config->source_key, $config->season_id, $config->team_id, 'team_page', $config->external_team_id);
        $run->updateMetadata(['url' => $config->team_season_url]);

        try {
            $html = $this->fetcher->fetch($config->team_season_url, $run);
            $extractedData = $this->extractor->extract($html);
            $tableDto = $extractedData['data'];

            $this->syncWithData($config, $tableDto);

            $run->finish([
                'extracted_count' => count($tableDto->rows),
                'imported_count' => count($tableDto->rows),
            ]);
        } catch (\Exception $e) {
            $run->fail($e);
            throw $e;
        }

        return $run;
    }

    /**
     * Synchronizuje soupisku s již vyparsovanými daty.
     */
    public function syncWithData(ExternalTeamSeasonConfig $config, \App\Services\Stats\DTO\NormalizedTableDTO $tableDto): void
    {
        $importedCount = 0;
        $skippedCount = 0;
        $warnings = [];

        DB::transaction(function () use ($tableDto, $config, &$importedCount, &$skippedCount, &$warnings) {
            $externalPlayerIdsOnRoster = [];

            foreach ($tableDto->rows as $row) {
                $externalPlayerId = $row->playerId;
                $playerName = $row->values['player_name'] ?? $row->rowLabel;

                if (! $externalPlayerId) {
                    $warnings[] = "Player '{$playerName}' has no external ID, skipping.";
                    $skippedCount++;

                    continue;
                }

                $externalPlayerIdsOnRoster[] = $externalPlayerId;

                // 1. Najít nebo vytvořit mapping a uživatele
                $user = $this->findOrCreateUserForExternalPlayer($externalPlayerId, $playerName, $config);

                // 2. Zajistit existenci PlayerProfile
                $profile = $user->playerProfile ?: $this->createPlayerProfile($user, $row->values);

                // 3. Aktualizovat pivot tabulku (is_on_roster = true)
                $this->updateRosterStatus($profile, $config->team_id, true);

                $importedCount++;
            }

            // 4. Hráči, kteří už nejsou na soupisce, nastavit is_on_roster = false
            $this->deactivateOldRosterMembers($config->team_id, $externalPlayerIdsOnRoster, $config->source_key);
        });
    }

    protected function findOrCreateUserForExternalPlayer(string $externalId, string $name, ExternalTeamSeasonConfig $config): User
    {
        // A. Hledat přes ExternalEntityMapping
        $mapping = ExternalEntityMapping::where([
            'source_key' => $config->source_key,
            'entity_type' => 'player',
            'external_id' => $externalId,
        ])->first();

        if ($mapping && $mapping->internal_id) {
            return User::findOrFail($mapping->internal_id);
        }

        // B. Hledat přes license_number (pokud ho známe z jiného zdroje/ručně)
        // Předpokládáme, že externalId z cz.basketball MŮŽE být license_number, ale nemusí.
        // V audit.md jsme zjistili, že externalId je ID z URL.
        $profile = PlayerProfile::where('license_number', $externalId)->first();
        if ($profile) {
            $user = $profile->user;
            $this->createMapping($user, $externalId, $config);

            return $user;
        }

        // C. Vytvořit "Ghost" uživatele
        return $this->createGhostUser($externalId, $name, $config);
    }

    protected function createGhostUser(string $externalId, string $name, ExternalTeamSeasonConfig $config): User
    {
        // Rozdělení jména
        $parts = explode(' ', $name);
        $firstName = $parts[0] ?? 'Ghost';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Player';

        // Placeholder email: ghost_{externalId}@{domain}
        $email = "ghost_{$config->source_key}_{$externalId}@kbelstisokoli.cz";

        $user = User::create([
            'name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'is_active' => false, // Neaktivní, dokud admin neschválí/nedoplní
            'metadata' => [
                'is_ghost' => true,
                'external_id' => $externalId,
                'source' => $config->source_key,
                'created_at_sync' => now()->toDateTimeString(),
            ],
        ]);

        $this->createMapping($user, $externalId, $config);

        return $user;
    }

    protected function createMapping(User $user, string $externalId, ExternalTeamSeasonConfig $config): void
    {
        ExternalEntityMapping::updateOrCreate([
            'source_key' => $config->source_key,
            'season_id' => $config->season_id,
            'entity_type' => 'player',
            'external_id' => $externalId,
        ], [
            'internal_type' => User::class,
            'internal_id' => $user->id,
            'identity_key' => $externalId, // Pro hráče používáme externalId jako identity_key
            'confidence' => 1.0,
            'last_seen_at' => now(),
        ]);
    }

    protected function createPlayerProfile(User $user, array $values): PlayerProfile
    {
        return PlayerProfile::create([
            'user_id' => $user->id,
            'license_number' => $values['license_number'] ?? null,
            'position' => $values['position'] ?? null,
            'height_cm' => $values['height_cm'] ?? null,
            'is_active' => true,
        ]);
    }

    protected function updateRosterStatus(PlayerProfile $profile, int $teamId, bool $isOnRoster): void
    {
        $profile->teams()->syncWithoutDetaching([
            $teamId => [
                'is_on_roster' => $isOnRoster,
                'active_from' => $isOnRoster ? now() : null,
            ],
        ]);

        $profile->load('teams');
    }

    protected function deactivateOldRosterMembers(int $teamId, array $currentExternalIds, string $sourceKey): void
    {
        // Najít všechny hráče v tomto týmu, kteří mají mapping pro tento zdroj,
        // ale jejich external_id není v seznamu aktuálních.

        $internalIdsOnRoster = ExternalEntityMapping::where('source_key', $sourceKey)
            ->where('entity_type', 'player')
            ->whereIn('external_id', $currentExternalIds)
            ->pluck('internal_id')
            ->toArray();

        // Hráči, kteří mají is_on_roster = true, ale nejsou v aktuálním seznamu
        $profilesToDeactivate = PlayerProfile::whereHas('teams', function ($query) use ($teamId) {
            $query->where('team_id', $teamId)
                ->where('is_on_roster', true);
        })->whereNotIn('user_id', $internalIdsOnRoster)->get();

        foreach ($profilesToDeactivate as $profile) {
            $this->updateRosterStatus($profile, $teamId, false);
        }
    }

    protected function getLastSnapshotPath(ExternalImportRun $run): ?string
    {
        // Tento helper by měl vrátit cestu, kterou fetcher uložil.
        // V CzBasketballFetcher jsme to ukládali do storage.
        return $run->metadata['snapshot_path'] ?? null;
    }
}
