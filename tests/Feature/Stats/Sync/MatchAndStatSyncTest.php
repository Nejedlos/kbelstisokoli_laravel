<?php

namespace Tests\Feature\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalEntityMapping;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Models\User;
use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use App\Services\Stats\Sync\MatchSyncService;
use App\Services\Stats\Sync\StatisticSyncService;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchAndStatSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_match_and_statistics()
    {
        $season = Season::create(['name' => '2025/2026', 'is_active' => true]);
        $team = Team::create(['name' => ['cs' => 'Sokol Kbely E'], 'slug' => 'sokol-kbely-e']);

        // Mock match list data
        $matchData = [
            'external_match_id' => '519196',
            'scheduled_at' => '2025-01-09 19:45:00',
            'home_team' => 'Baník Praha',
            'away_team' => 'Sokol Kbely E',
            'score' => '56:60',
            'status' => 'completed',
        ];

        $matchSync = $this->app->make(MatchSyncService::class);
        $match = $matchSync->sync($team, $season, $matchData);

        $this->assertNotNull($match);
        $this->assertEquals(56, $match->score_home);
        $this->assertEquals(60, $match->score_away);
        $this->assertFalse($match->is_home);
        $this->assertEquals('Baník Praha', $match->opponent->name);

        // Mock boxscore data from fixture
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
        $extractor = $this->app->make(MatchDetailBoxscoreExtractor::class);
        $extracted = $extractor->extract($html);

        // Marek Novotný has external_id 11246
        $user = User::create([
            'name' => 'Marek Novotný',
            'email' => 'marek@example.com',
            'password' => 'pass'
        ]);
        ExternalEntityMapping::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'entity_type' => 'player',
            'external_id' => '11246',
            'internal_type' => User::class,
            'internal_id' => $user->id,
            'identity_key' => '11246',
        ]);

        $statSync = $this->app->make(StatisticSyncService::class);
        $statSync->syncMatchBoxscore($match, $extracted['data']);

        // Check statistic rows
        $boxscoreSet = StatisticSet::where('slug', StatisticSetService::MATCH_BOXSCORE_SET)->first();
        $this->assertNotNull($boxscoreSet);

        // Check Marek Novotny's row
        $row = StatisticRow::where('statistic_set_id', $boxscoreSet->id)
            ->where('player_id', $user->id)
            ->where('basketball_match_id', $match->id)
            ->first();

        $this->assertNotNull($row);
        $this->assertEquals(9, $row->values['pts'] ?? 0);
        $this->assertEquals(3, $row->values['fg3_made'] ?? 0);

        // Check recomputations
        $summarySet = StatisticSet::where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET)->first();
        $summaryRow = StatisticRow::where('statistic_set_id', $summarySet->id)
            ->where('player_id', $user->id)
            ->where('season_id', $season->id)
            ->first();

        $this->assertNotNull($summaryRow);
        $this->assertEquals(9, $summaryRow->values['pts_total']);
        $this->assertEquals(1, $summaryRow->values['gp']);
    }
}
