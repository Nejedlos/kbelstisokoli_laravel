<?php

namespace App\Services\Stats\Sync;

use App\Models\ExternalImportRun;
use App\Models\PlayerProfile;
use App\Models\User;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\PlayerDetailExtractor;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PlayerSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected PlayerDetailExtractor $extractor
    ) {}

    /**
     * Synchronizuje detail hráče z cz.basketball.
     */
    public function syncPlayer(User $user, array $options = []): bool
    {
        // Najdeme externí ID pro czbasketball
        $mapping = $user->externalMappings()
            ->where('source_key', 'czbasketball')
            ->first();

        if (!$mapping || !$mapping->external_id) {
            Log::warning("PlayerSyncService: User {$user->display_name} has no czbasketball external_id.");
            return false;
        }

        $extId = $mapping->external_id;
        $url = "https://cz.basketball/hrac/{$extId}";

        // Vytvoříme běh importu pro logování
        $seasonId = \App\Models\Season::where('is_active', true)->first()?->id ?? 0;
        $run = \App\Models\ExternalImportRun::start('czbasketball', $seasonId, null, 'player_detail', $extId);

        try {
            if (method_exists($this->fetcher, 'setCurrentRun')) {
                $this->fetcher->setCurrentRun($run);
            }

            $html = $this->fetcher->fetch($url);
            $result = $this->extractor->extract($html);
            $data = $result['data'];

            // 1. Aktualizace PlayerProfile (Základní info)
            $profile = $user->playerProfiles()->first() ?: new \App\Models\PlayerProfile(['user_id' => $user->id]);

            $position = $this->normalizePosition($data['position'] ?? null);
            if ($position) {
                $profile->position = $position;
            }

            if ($data['height']) {
                $profile->height_cm = $data['height'];
            }

            // Metadata - uložení celé historie a extrahovaných dat (zůstává jako backup)
            $metadata = $profile->metadata ?? [];
            $metadata['external_data'] = $data;
            $metadata['last_sync_at'] = now()->toDateTimeString();
            $profile->metadata = $metadata;

            $profile->save();

            // 2. Fotografie
            if (!empty($data['photo_url'])) {
                $this->syncPhoto($user, $data['photo_url']);
            }

            // 3. Detailní statistiky do nové tabulky external_player_stats
            if (!empty($data['stats'])) {
                foreach ($data['stats'] as $statData) {
                    \App\Models\ExternalPlayerStat::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'source_key' => 'czbasketball',
                            'season_label' => $statData['season_label'] ?? null,
                            'competition_label' => $statData['competition_label'] ?? null,
                            'team_name' => $statData['team_name'] ?? null,
                            'is_career_total' => $statData['is_career_total'] ?? false,
                        ],
                        array_merge($statData, [
                            'external_id' => $extId,
                        ])
                    );
                }
            }

            // 4. Historie zápasů do nové tabulky external_player_matches
            if (!empty($data['matches'])) {
                foreach ($data['matches'] as $matchData) {
                    \App\Models\ExternalPlayerMatch::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'source_key' => 'czbasketball',
                            'external_match_id' => $matchData['external_match_id'] ?? null,
                            'match_date' => $matchData['match_date'],
                            'opponent_name' => $matchData['opponent_name'] ?? null,
                        ],
                        array_merge($matchData, [
                            'external_id' => $extId,
                        ])
                    );
                }
            }

            $run->finish([
                'imported_count' => count($data['stats'] ?? []),
                'matches_count' => count($data['matches'] ?? [])
            ]);
            Log::info("PlayerSyncService: Successfully synced player {$user->display_name} (ExtID: {$extId}), " . count($data['stats'] ?? []) . " stat rows and " . count($data['matches'] ?? []) . " matches.");

            return true;
        } catch (\Exception $e) {
            $run->fail($e);
            Log::error("PlayerSyncService: Failed to sync player {$user->display_name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Synchronizuje fotografii do Media Library.
     */
    public function syncPhoto(User $user, string $photoUrl): void
    {
        try {
            // Kontrola, zda už fotku v portfoliu nemá
            $alreadyHas = $user->getMedia('player_photos')->contains(function (Media $media) use ($photoUrl) {
                return $media->getCustomProperty('source_url') === $photoUrl;
            });

            if (!$alreadyHas) {
                // Musíme ošetřit název souboru, protože cz.basketball používá min.php?...,
                // což MediaLibrary odmítá jako PHP soubor.
                $fileName = 'player_' . $user->id . '_' . md5($photoUrl) . '.jpg';

                $user->addMediaFromUrl($photoUrl)
                    ->usingFileName($fileName)
                    ->withCustomProperties([
                        'source_url' => $photoUrl,
                        'added_from' => 'player_detail_sync',
                        'synced_at' => now()->toDateTimeString()
                    ])
                    ->toMediaCollection('player_photos');

                Log::info("PlayerSyncService: Added new photo to player {$user->display_name} from {$photoUrl}");
            }
        } catch (\Exception $e) {
            Log::warning("PlayerSyncService: Failed to download photo for {$user->display_name}: " . $e->getMessage());
        }
    }

    /**
     * Normalizuje pozici z cz.basketball na náš Enum.
     */
    protected function normalizePosition(?string $rawPosition): ?\App\Enums\BasketballPosition
    {
        if (!$rawPosition) return null;

        $rawPosition = mb_strtoupper(trim($rawPosition));

        // Mapování čísel (často používané v basketbalu)
        $map = [
            '1' => \App\Enums\BasketballPosition::PG,
            '2' => \App\Enums\BasketballPosition::SG,
            '3' => \App\Enums\BasketballPosition::SF,
            '4' => \App\Enums\BasketballPosition::PF,
            '5' => \App\Enums\BasketballPosition::C,
            'PG' => \App\Enums\BasketballPosition::PG,
            'SG' => \App\Enums\BasketballPosition::SG,
            'SF' => \App\Enums\BasketballPosition::SF,
            'PF' => \App\Enums\BasketballPosition::PF,
            'C' => \App\Enums\BasketballPosition::C,
            'G' => \App\Enums\BasketballPosition::PG, // Guard -> PG
            'F' => \App\Enums\BasketballPosition::SF, // Forward -> SF
        ];

        return $map[$rawPosition] ?? null;
    }
}
