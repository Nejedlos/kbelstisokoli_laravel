<?php

namespace Tests\Unit\Services\Stats;

use App\Models\ExternalPlayerStat;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\User;
use App\Services\Stats\PlayerStatsService;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PlayerStatsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PlayerStatsService::class);
    }

    public function test_get_career_overview_excludes_unknown_seasons()
    {
        // 1. Vytvoříme uživatele
        $user = User::factory()->create();

        // 2. Vytvoříme platnou a neznámou sezónu v externích datech
        ExternalPlayerStat::create([
            'user_id' => $user->id,
            'source_key' => 'test',
            'external_id' => '1',
            'season_label' => '2023/2024',
            'competition_label' => 'Liga A',
            'team_name' => 'Tým A',
            'games_played' => 10,
            'points_avg' => 15.0,
            'is_career_total' => false,
        ]);

        ExternalPlayerStat::create([
            'user_id' => $user->id,
            'source_key' => 'test',
            'external_id' => '2',
            'season_label' => 'Neznámá sezóna',
            'competition_label' => 'Liga B',
            'team_name' => 'Tým B',
            'games_played' => 5,
            'points_avg' => 20.0,
            'is_career_total' => false,
        ]);

        // 3. Vytvoříme platnou a neznámou sezónu v interních datech
        $validSeason = Season::create(['name' => '2024/2025', 'is_active' => true]);

        $setService = app(StatisticSetService::class);
        $set = $setService->ensureSet(StatisticSetService::PLAYER_SEASON_SUMMARY_SET, 'Test Set', 'player');

        StatisticRow::create([
            'statistic_set_id' => $set->id,
            'player_id' => $user->id,
            'season_id' => $validSeason->id,
            'values' => [
                'gp' => 12,
                'pts_total' => 120,
                'efficiency_total' => 100,
                'rebounds_total' => 50,
                'assists_total' => 30,
            ],
        ]);

        // Řádek bez sezóny (představuje "Neznámou sezónu" v PlayerStatsService logic)
        StatisticRow::create([
            'statistic_set_id' => $set->id,
            'player_id' => $user->id,
            'season_id' => null,
            'values' => [
                'gp' => 8,
                'pts_total' => 80,
            ],
        ]);

        // 4. Zavoláme službu
        $overview = $this->service->getCareerOverview($user->id);

        // 5. Ověříme výsledky
        $history = $overview['history'];

        // Měly by tam být jen 2 sezóny (2023/2024 a 2024/2025)
        $this->assertCount(2, $history);

        $seasons = collect($history)->pluck('season')->toArray();
        $this->assertContains('2023/2024', $seasons);
        $this->assertContains('2024/2025', $seasons);

        foreach ($seasons as $s) {
            $this->assertNotEquals('Neznámá sezóna', $s);
        }

        // Ověříme celkový součet GP - měl by být 10 + 12 = 22
        $this->assertEquals(22, $overview['summary']['total_gp']);
    }
}
