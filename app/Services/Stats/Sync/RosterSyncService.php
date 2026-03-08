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
        protected TeamRosterExtractor $extractor,
        protected PlayerSyncService $playerSyncService
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
            $fragmentHtml = $extractedData['fragment_html'] ?? '';

            $hash = hash('sha256', $fragmentHtml);

            if ($run->isIdenticalToLast($hash)) {
                $run->skip();

                return $run;
            }

            $run->update(['content_hash' => $hash]);

            // Uložíme název týmu do konfigurace, pokud tam ještě není (pro boxscore párování)
            if (empty($config->team_name_in_source) && isset($tableDto->metadata['team_name'])) {
                $config->update(['team_name_in_source' => $tableDto->metadata['team_name']]);
                $run->addLog('info', null, null, null, "Zjištěn název týmu ve zdroji: {$tableDto->metadata['team_name']}");
            }

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

                // 2. Zajistit existenci a aktualizaci PlayerProfile
                $profile = $user->playerProfile;
                if (! $profile) {
                    $profile = $this->createPlayerProfile($user, $row->values);
                } else {
                    $this->updatePlayerProfile($profile, $row->values);
                }

                // 3. Aktualizovat pivot tabulku (is_on_roster = true)
                $this->updateRosterStatus($profile, $config->team_id, true);

                // 4. Synchronizovat detail hráče (fotka, historie, atd.)
                $this->playerSyncService->syncPlayer($user);

                $importedCount++;
            }

            // 4. Hráči, kteří už nejsou na soupisce, nastavit is_on_roster = false
            $this->deactivateOldRosterMembers($config->team_id, $externalPlayerIdsOnRoster, $config->source_key);
        });
    }

    protected function findOrCreateUserForExternalPlayer(string $externalId, string $name, ExternalTeamSeasonConfig $config): User
    {
        // A. Hledat přes ExternalEntityMapping (již dříve spárovaní hráči v libovolné sezóně)
        $mapping = ExternalEntityMapping::where([
            'source_key' => $config->source_key,
            'entity_type' => 'player',
            'external_id' => $externalId,
        ])->first();

        if ($mapping && $mapping->internal_id) {
            return User::findOrFail($mapping->internal_id);
        }

        // B. Hledat přes license_number (pokud ho známe z jiného zdroje/ručně)
        $profile = PlayerProfile::where('license_number', $externalId)->first();
        if ($profile) {
            $user = $profile->user;
            $this->createMapping($user, $externalId, $config);

            return $user;
        }

        // C. Hledat uživatele podle jména (NAME MATCH)
        // Zkusíme najít reálného uživatele se stejným jménem, abychom předešli duplicitám (ghostům)
        $userByName = $this->findUserByName($name);
        if ($userByName) {
            $this->createMapping($userByName, $externalId, $config);

            return $userByName;
        }

        // D. Vytvořit "Ghost" uživatele
        return $this->createGhostUser($externalId, $name, $config);
    }

    /**
     * Pokusí se najít uživatele podle jména (zkouší různé kombinace).
     * Vrací uživatele pouze pokud je nalezen právě jeden reálný kandidát.
     */
    protected function findUserByName(string $externalName): ?User
    {
        $parts = explode(' ', trim($externalName));
        if (count($parts) < 2) {
            return null;
        }

        $p1 = $parts[0];
        $p2 = implode(' ', array_slice($parts, 1));

        // Hledáme pouze mezi reálnými uživateli (ne ghosty)
        $query = User::where(function ($q) {
            $q->whereNull('email')
                ->orWhere('email', 'NOT LIKE', 'ghost_%');
        });

        $candidates = $query->where(function ($q) use ($externalName, $p1, $p2) {
            $q->where('name', $externalName)
                ->orWhere('name', "{$p2} {$p1}")
                ->orWhere(function ($q2) use ($p1, $p2) {
                    $q2->where('first_name', $p1)->where('last_name', $p2);
                })
                ->orWhere(function ($q2) use ($p1, $p2) {
                    $q2->where('first_name', $p2)->where('last_name', $p1);
                });
        })->get();

        // Pokud najdeme právě jednoho kandidáta, považujeme to za shodu
        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        return null;
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
        $data = $this->prepareProfileData($values);
        $data['user_id'] = $user->id;
        $data['is_active'] = true;

        return PlayerProfile::create($data);
    }

    protected function updatePlayerProfile(PlayerProfile $profile, array $values): void
    {
        $data = $this->prepareProfileData($values);

        // Aktualizujeme jen pokud jsou hodnoty nové a nejsou v profilu
        $toUpdate = [];
        foreach ($data as $key => $value) {
            if ($value !== null && (empty($profile->{$key}) || $key === 'metadata')) {
                if ($key === 'metadata') {
                    $toUpdate[$key] = array_merge($profile->metadata ?? [], $value);
                } else {
                    $toUpdate[$key] = $value;
                }
            }
        }

        if (!empty($toUpdate)) {
            $profile->update($toUpdate);
        }
    }

    protected function prepareProfileData(array $values): array
    {
        $metadata = [];
        if (!empty($values['birth_year'])) {
            $metadata['birth_year'] = $values['birth_year'];
        }
        if (!empty($values['nationality'])) {
            $metadata['nationality'] = $values['nationality'];
        }

        return [
            'jersey_number' => $values['jersey_number'] ?? null,
            'position' => $this->mapPosition($values['position'] ?? null),
            'height_cm' => $values['height'] ?? null,
            'weight_kg' => $values['weight'] ?? null,
            'metadata' => !empty($metadata) ? $metadata : null,
        ];
    }

    protected function mapPosition(?string $pos): ?string
    {
        if (!$pos) return null;

        $pos = mb_strtoupper(trim($pos));
        return match ($pos) {
            '1', 'PG' => 'PG',
            '2', 'SG', 'G' => 'SG',
            '3', 'SF', 'F' => 'SF',
            '4', 'PF' => 'PF',
            '5', 'C' => 'C',
            default => null,
        };
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
