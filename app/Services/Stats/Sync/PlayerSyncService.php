<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalEntityMapping;
use App\Models\ExternalImportRun;
use App\Models\ExternalPlayerMatch;
use App\Models\Opponent;
use App\Models\PlayerProfile;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\PlayerDetailExtractor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PlayerSyncService
{
    public function __construct(
        protected StatFetcherInterface $fetcher,
        protected PlayerDetailExtractor $extractor,
        protected StatisticSyncService $statisticSyncService,
        protected \App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor $matchExtractor
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

            // Rekordy do metadat profilu pro "excesivní" uložení
            if (!empty($data['records'])) {
                $metadata['records'] = $data['records'];
            }

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

            // 5. Excesivní synchronizace historie (všechny dostupné sezóny a detaily zápasů)
            if ($options['excesive'] ?? true) {
                $this->syncExcesiveHistory($user, $data['available_seasons'] ?? [], $run);
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

    /**
     * Excesivní synchronizace historie (všechny dostupné sezóny a detaily zápasů).
     */
    public function syncExcesiveHistory(User $user, array $seasons, ?ExternalImportRun $run): void
    {
        $mapping = $user->externalMappings()->where('source_key', 'czbasketball')->first();
        if (!$mapping) return;

        $extId = $mapping->external_id;

        foreach ($seasons as $season) {
            $year = substr($season, 0, 4);
            $url = "https://cz.basketball/hrac/{$extId}?tab=matches&y=" . $year;
            try {
                $html = $this->fetcher->fetch($url);
                $result = $this->extractor->extract($html);
                $matches = $result['data']['matches'] ?? [];

                foreach ($matches as $matchData) {
                    $extMatchId = $matchData['external_match_id'] ?? null;
                    if (!$extMatchId) continue;

                    // Uložíme základ
                    \App\Models\ExternalPlayerMatch::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'source_key' => 'czbasketball',
                            'external_match_id' => (string) $extMatchId,
                        ],
                        array_merge($matchData, ['external_id' => $extId])
                    );

                    // A nyní detailní boxscore "excesivně"
                    // Stahujeme pouze pokud ještě nemáme scheduled_at (indikátor že jsme aspoň jednou stáhli detail)
                    // nebo u odehraných zápasů pokud nemáme asistence (indikátor statistik)
                    $exists = \App\Models\ExternalPlayerMatch::where('user_id', $user->id)
                        ->where('external_match_id', (string) $extMatchId)
                        ->first();

                    $hasScheduledAt = $exists && $exists->scheduled_at !== null;
                    $hasStats = $exists && $exists->assists !== null;
                    $isPast = isset($matchData['match_date']) && $matchData['match_date'] <= now()->format('Y-m-d');
                    $hasPoints = isset($matchData['points']) && $matchData['points'] > 0;

                    if (!$hasScheduledAt || ($isPast && !$hasStats)) {
                        $this->syncExternalMatchDetail($user, (string) $extMatchId, $run);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("PlayerSyncService: Failed to sync history for season $season for player {$user->display_name}: " . $e->getMessage());
            }
        }
    }

    /**
     * Synchronizuje detail zápasu (boxscore) i pro zápasy, které nejsou v naší DB.
     */
    public function syncExternalMatchDetail(User $user, string $extMatchId, ?ExternalImportRun $run): void
    {
        $url = "https://cz.basketball/zapas/{$extMatchId}";
        try {
            $html = $this->fetcher->fetch($url);
            $boxscoreData = $this->matchExtractor->extract($html);

            $matchHeader = $boxscoreData['data']->metadata['header'] ?? [];
            $matchDate = $this->parseMatchDate($matchHeader['date'] ?? null);
            $scheduledAt = $matchHeader['scheduled_at'] ?? null;
            $competitionLabel = $matchHeader['competition'] ?? null;
            $venue = $matchHeader['venue'] ?? null;

            // Pokud nejsou žádné tabulky (budoucí zápas), zkusíme aktualizovat aspoň metadata pro aktuálního hráče
            if (empty($boxscoreData['tables'] ?? [])) {
                $mapping = $user->externalMappings()
                    ->where('source_key', 'czbasketball')
                    ->first();

                // Pokusíme se zjistit soupeře z hlavičky.
                // Pokud nemáme tabulku, musíme se spolehnout na to, co už máme v DB nebo co je v hlavičce.
                $existingMatch = \App\Models\ExternalPlayerMatch::where('user_id', $user->id)
                    ->where('external_match_id', (string) $extMatchId)
                    ->first();
                $opponentName = $existingMatch?->opponent_name;

                // Pokud v DB soupeře nemáme, zkusíme ho detekovat z hlavičky (pokud známe tým hráče)
                if (!$opponentName && !empty($matchHeader['home_team']) && !empty($matchHeader['away_team'])) {
                    // Tady je to ošemetné, nevíme jistě za koho hraje, ale můžeme zkusit match_header
                    // Pro teď necháme to co je v DB nebo co se už uložilo ze seznamu.
                }

                $extPlayerMatch = $this->statisticSyncService->updateExternalPlayerMatch(
                    userId: $user->id,
                    externalMatchId: $extMatchId,
                    externalPlayerId: $mapping?->external_id,
                    rowValues: [],
                    rowMetadata: [],
                    matchInfo: [
                        'match_date' => $matchDate,
                        'scheduled_at' => $scheduledAt,
                        'competition_label' => $competitionLabel,
                        'opponent_name' => $opponentName,
                        'venue' => $venue,
                        'source_key' => 'czbasketball',
                        'metadata' => $matchHeader,
                    ]
                );

                $this->ensureBasketballMatch($extPlayerMatch, $matchHeader);
            }

            // Zpracujeme všechny tabulky statistik v boxscoru (pokud existují)
            foreach ($boxscoreData['tables'] ?? [] as $table) {
                foreach ($table->rows as $row) {
                    $extPlayerId = $row->metadata['external_player_id'] ?? $row->playerId;
                    if (!$extPlayerId) continue;

                    // Zkusíme najít našeho hráče (podle externího ID)
                    $mapping = ExternalEntityMapping::where([
                        'source_key' => 'czbasketball',
                        'entity_type' => 'player',
                        'external_id' => (string) $extPlayerId,
                    ])->first();

                    if ($mapping && $mapping->internal_id) {
                        $extPlayerMatch = $this->statisticSyncService->updateExternalPlayerMatch(
                            userId: $mapping->internal_id,
                            externalMatchId: $extMatchId,
                            externalPlayerId: (string) $extPlayerId,
                            rowValues: $row->values,
                            rowMetadata: $row->metadata ?? [],
                            matchInfo: [
                                'match_date' => $matchDate,
                                'scheduled_at' => $scheduledAt,
                                'competition_label' => $competitionLabel,
                                'opponent_name' => $this->detectOpponent($matchHeader, $table->name),
                                'venue' => $venue,
                                'source_key' => 'czbasketball',
                                'metadata' => $matchHeader,
                            ]
                        );

                        if ($mapping->internal_id === $user->id) {
                            $this->ensureBasketballMatch($extPlayerMatch, $matchHeader);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("PlayerSyncService: Failed to sync match detail $extMatchId: " . $e->getMessage());
        }
    }

    protected function parseMatchDate(?string $dateStr): ?string
    {
        if (!$dateStr) return null;
        // "26. 11. 2025" -> "2025-11-26"
        if (preg_match('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }

    protected function detectOpponent(array $header, string $tableName): ?string
    {
        $teamA = $header['home_team'] ?? null;
        $teamB = $header['away_team'] ?? null;

        if (!$teamA || !$teamB) return null;

        // Pokud je název tabulky (týmu) Team A, pak soupeř je Team B a naopak
        if (mb_stripos($tableName, $teamA) !== false) {
            return $teamB;
        }
        return $teamA;
    }

    /**
     * Zajistí existenci haly (Venue) podle názvu.
     */
    protected function ensureVenue(?string $name): ?Venue
    {
        if (!$name || strlen(trim($name)) < 2) return null;

        $name = trim($name);

        // Zkusíme najít podle přesného názvu
        $venue = Venue::where('name', $name)->first();
        if ($venue) return $venue;

        // Zkusíme najít v metadatech (původní názvy)
        $venue = Venue::whereJsonContains('metadata->original_names', $name)->first();
        if ($venue) return $venue;

        // Vytvoříme novou halu
        return Venue::create([
            'name' => $name,
            'metadata' => [
                'original_names' => [$name],
                'source' => 'czbasketball'
            ]
        ]);
    }

    /**
     * Zajistí existenci BasketballMatch a propojí ho s ExternalPlayerMatch.
     */
    public function ensureBasketballMatch(?ExternalPlayerMatch $extMatch, array $header): ?BasketballMatch
    {
        if (!$extMatch) return null;

        // 1. Identifikace našeho týmu a soupeře
        $homeTeamName = $header['home_team'] ?? null;
        $awayTeamName = $header['away_team'] ?? null;
        if (!$homeTeamName || !$awayTeamName) return null;

        $ourTeam = null;
        $opponentName = null;
        $isHome = false;

        $teams = Team::all();
        foreach ($teams as $team) {
            $normalizedOur = $this->statisticSyncService->normalizeForComparison($team->name);
            if (str_contains($this->statisticSyncService->normalizeForComparison($homeTeamName), $normalizedOur)) {
                $ourTeam = $team;
                $opponentName = $awayTeamName;
                $isHome = true;
                break;
            }
            if (str_contains($this->statisticSyncService->normalizeForComparison($awayTeamName), $normalizedOur)) {
                $ourTeam = $team;
                $opponentName = $homeTeamName;
                $isHome = false;
                break;
            }
        }

        if (!$ourTeam) return null;

        // 2. Najdeme nebo vytvoříme oponenta
        $opponent = Opponent::firstOrCreate(['name' => $opponentName]);

        // 3. Najdeme nebo vytvoříme sezónu
        $matchDate = $extMatch->scheduled_at ?: ($extMatch->match_date ? $extMatch->match_date->startOfDay() : now());
        $year = (int)$matchDate->format('Y');
        $month = (int)$matchDate->format('m');
        $seasonName = ($month >= 8) ? "$year/" . ($year + 1) : ($year - 1) . "/$year";
        $season = Season::firstOrCreate(['name' => $seasonName]);

        // 3.5 Najdeme nebo vytvoříme halu (Venue)
        $venueName = $header['venue'] ?? $extMatch->venue;
        $venue = $this->ensureVenue($venueName);

        // 4. Najdeme nebo vytvoříme BasketballMatch
        $match = BasketballMatch::where(function($q) use ($extMatch) {
                $q->where('metadata->external_id', $extMatch->external_match_id)
                  ->orWhere('metadata->external_match_id', $extMatch->external_match_id);
            })
            ->orWhere(function($q) use ($extMatch, $ourTeam) {
                $q->where('team_id', $ourTeam->id)
                  ->where('scheduled_at', $extMatch->scheduled_at);
            })
            ->first();

        if (!$match) {
            $match = new BasketballMatch();
            $match->team_id = $ourTeam->id;
            $match->opponent_id = $opponent->id;
            $match->season_id = $season->id;
            $match->match_type = 'league';
            $match->status = 'scheduled';
        }

        // 5. Aktualizace dat
        $match->scheduled_at = $extMatch->scheduled_at ?: $match->scheduled_at;
        $match->location = $extMatch->venue ?: $match->location;
        $match->venue_id = $venue?->id ?: $match->venue_id;
        $match->is_home = $isHome;

        // Pokud máme halu a tým ji nemá, nastavíme ji jako primární pro domácí zápasy
        if ($venue) {
            if ($isHome) {
                if (!$ourTeam->primary_venue_id) {
                    $ourTeam->primary_venue_id = $venue->id;
                    $ourTeam->save();
                }
            } else {
                if ($opponent && !$opponent->primary_venue_id) {
                    $opponent->primary_venue_id = $venue->id;
                    $opponent->save();
                }
            }
        }

        if (!empty($header['score']) && preg_match('/(\d+)\s*:\s*(\d+)/', $header['score'], $m)) {
            $match->score_home = (int)$m[1];
            $match->score_away = (int)$m[2];
            $match->status = 'completed';
        }

        $metadata = $match->metadata ?? [];
        $metadata['external_id'] = $extMatch->external_match_id;
        $metadata['source'] = 'czbasketball';
        $metadata['match_header'] = $header;
        $match->metadata = $metadata;

        $match->save();

        // 6. Propojíme s ExternalPlayerMatch
        if ($extMatch->basketball_match_id !== $match->id) {
            $extMatch->basketball_match_id = $match->id;
            $extMatch->save();
        }

        return $match;
    }
}
