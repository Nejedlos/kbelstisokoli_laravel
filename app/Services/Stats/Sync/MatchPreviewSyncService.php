<?php

namespace App\Services\Stats\Sync;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\ExternalTeamSeasonConfig;
use App\Services\Stats\Contracts\StatFetcherInterface;
use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MatchPreviewSyncService
{
    public function __construct(private StatFetcherInterface $fetcher, private MatchDetailBoxscoreExtractor $extractor) {}

    public function sync(int $matchId, int $teamId, bool $force = false): void
    {
        $match = BasketballMatch::find($matchId);
        $config = $match ? $this->configFor($match, $teamId) : null;
        if (! $match || ! $config || ! $this->eligible($match, $config, $teamId, $force)) {
            return;
        }

        $externalId = (string) ($match->metadata['external_id'] ?? $match->metadata['season_external_match_id'] ?? '');
        $host = strtolower((string) parse_url($config->matches_list_url, PHP_URL_HOST));
        $url = 'https://'.$host.'/zapas/'.$externalId;
        $data = $this->extractor->extract($this->fetcher->fetch($url))['data']->metadata;
        $header = $data['header'] ?? [];
        if (! $this->identityMatches($match, $config, $header)) {
            return;
        }

        DB::transaction(function () use ($matchId, $teamId, $force, $externalId, $host, $header, $data) {
            $fresh = BasketballMatch::whereKey($matchId)->lockForUpdate()->first();
            $config = $fresh ? $this->configFor($fresh, $teamId) : null;
            if (! $fresh || ! $config || ! $this->eligible($fresh, $config, $teamId, $force)
                || (string) ($fresh->metadata['external_id'] ?? $fresh->metadata['season_external_match_id'] ?? '') !== $externalId
                || strtolower((string) parse_url($config->matches_list_url, PHP_URL_HOST)) !== $host
                || ! $this->identityMatches($fresh, $config, $header)) {
                return;
            }
            $metadata = $fresh->metadata ?? [];
            $metadata['official_team_name'] = $fresh->is_home ? $header['home_team'] : $header['away_team'];
            $metadata['official_opponent_name'] = $fresh->is_home ? $header['away_team'] : $header['home_team'];
            foreach (['last_matches', 'team_comparison', 'mutual_matches'] as $key) {
                $metadata[$key] = $data[$key] ?? [];
            }
            $metadata['preview_synced_at'] = now()->toIso8601String();
            $fresh->update(['metadata' => $metadata]);
            ComputeMatchPredictionJob::dispatch($fresh->id)->afterCommit();
        });
    }

    private function configFor(BasketballMatch $match, int $teamId): ?ExternalTeamSeasonConfig
    {
        return ExternalTeamSeasonConfig::where('team_id', $teamId)->where('season_id', $match->season_id)
            ->where('source_key', 'czbasketball')->get()->first(fn ($config) => $config->isMatchSyncEnabled());
    }

    private function eligible(BasketballMatch $match, ExternalTeamSeasonConfig $config, int $teamId, bool $force): bool
    {
        $host = strtolower((string) parse_url($config->matches_list_url ?? '', PHP_URL_HOST));
        $externalId = (string) ($match->metadata['external_id'] ?? $match->metadata['season_external_match_id'] ?? '');
        if ($match->team_id !== $teamId || ! $match->hasPredictionContext() || ! $match->season?->is_active
            || ! in_array($match->status, ['planned', 'scheduled'], true) || ! $match->scheduled_at?->isFuture()
            || $match->score_home !== null || $match->score_away !== null || $match->is_home === null
            || ! preg_match('/^[0-9]+$/D', $externalId) || ! ($host === 'cz.basketball' || str_ends_with($host, '.cz.basketball'))
            || blank($config->team_name_in_source)) {
            return false;
        }
        if (! $force && ! empty($match->metadata['preview_synced_at'])) {
            try {
                if (Carbon::parse($match->metadata['preview_synced_at'])->gt(now()->subDay())) {
                    return false;
                }
            } catch (\Exception $e) {
                // An invalid legacy timestamp must not prevent a fresh import.
            }
        }

        return true;
    }

    private function identityMatches(BasketballMatch $match, ExternalTeamSeasonConfig $config, array $header): bool
    {
        $normalize = fn ($name) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $name)));
        $team = $normalize($config->team_name_in_source);
        $opponent = $normalize($match->official_opponent_name);
        $home = $normalize($header['home_team'] ?? '');
        $away = $normalize($header['away_team'] ?? '');

        return $team !== '' && $opponent !== '' && $team !== $opponent
            && ($match->is_home ? ($home === $team && $away === $opponent) : ($away === $team && $home === $opponent));
    }
}
