<?php

namespace Tests\Feature\Stats\Extractors;

use App\Services\Stats\Extractors\CzBasketball\DOM\CzBasketballMatchDetailDomExtractor;
use App\Services\Stats\Extractors\CzBasketball\DOM\CzBasketballMatchesListDomExtractor;
use App\Services\Stats\Extractors\CzBasketball\DOM\CzBasketballPlayerDomExtractor;
use App\Services\Stats\Extractors\CzBasketball\DOM\CzBasketballTeamPageDomExtractor;
use Tests\TestCase;

class CzBasketballDomExtractorTest extends TestCase
{
    public function test_team_page_dom_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
        $extractor = new CzBasketballTeamPageDomExtractor();

        $result = $extractor->extract($html, 7738, 2025);

        $this->assertEquals('Sokol Kbely E', $result->team_header['team_name']);
        $this->assertGreaterThan(0, count($result->roster_table));
        $this->assertGreaterThan(0, count($result->matches_table));
        $this->assertNotEmpty($result->links['player_external_ids']);
        $this->assertNotEmpty($result->links['match_external_ids']);

        // Check player values
        $player = $result->roster_table[0];
        $this->assertArrayHasKey('player_external_id', $player);
        $this->assertArrayHasKey('ft_made_pg', $player);
        $this->assertArrayHasKey('ft_att_pg', $player);
    }

    public function test_matches_list_dom_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'));
        $extractor = new CzBasketballMatchesListDomExtractor();

        $rows = $extractor->extract($html);

        $this->assertGreaterThanOrEqual(1, count($rows));
        $this->assertNotEmpty($rows[0]['match_external_id']);
    }

    public function test_match_detail_dom_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
        $extractor = new CzBasketballMatchDetailDomExtractor();

        $result = $extractor->extract($html);

        $this->assertNotEmpty($result['header']['home_team']);
        $this->assertNotEmpty($result['header']['away_team']);
        $this->assertGreaterThan(0, count($result['team_blocks']));

        $firstTeam = $result['team_blocks'][0];
        $this->assertGreaterThan(0, count($firstTeam['rows']));
        $this->assertNotEmpty($firstTeam['rows'][0]['player_name']);
        $this->assertArrayHasKey('ft_made', $firstTeam['rows'][0]['values']);
    }

    public function test_player_page_dom_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/player_page.html'));
        $extractor = new CzBasketballPlayerDomExtractor();
        $result = $extractor->extract($html);

        $this->assertCount(1, $result['career_table']);
        $this->assertEquals('Sokol Kbely E', $result['career_table'][0]['team_name']);
        $this->assertEquals('1.7', $result['career_table'][0]['ft_made_pg']);

        $this->assertCount(1, $result['per_game_list']);
        $this->assertEquals('Baník Praha', $result['per_game_list'][0]['opponent_name']);
        $this->assertEquals('15', $result['per_game_list'][0]['pts']);

        $this->assertCount(1, $result['opponent_summary']);
        $this->assertEquals('Baník Praha', $result['opponent_summary'][0]['opponent_name']);

        $this->assertEquals('Sokol Kbely E', $result['current_club']['club_name']);
    }
}
