<?php

namespace Tests\Feature\Stats\Extractors;

use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;
use Tests\TestCase;

class CzBasketballExtractorTest extends TestCase
{
    public function test_team_roster_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
        $extractor = new TeamRosterExtractor();

        $result = $extractor->extract($html);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('fragment_html', $result);
        $this->assertNotEmpty($result['fragment_html']);

        $dto = $result['data'];
        $this->assertEquals('Soupiska', $dto->name);
        $this->assertGreaterThan(0, count($dto->rows));

        // Zkusíme najít aspoň jednoho hráče s ID
        $foundId = false;
        foreach ($dto->rows as $row) {
            if ($row->metadata['external_player_id'] ?? null) {
                $foundId = true;
                break;
            }
        }
        $this->assertTrue($foundId, 'Should find at least one player with external ID');
    }

    public function test_matches_list_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/matches_list.html'));
        $extractor = new MatchesListExtractor();

        $result = $extractor->extract($html);

        $this->assertArrayHasKey('data', $result);
        $dto = $result['data'];

        $this->assertGreaterThan(0, count($dto->rows));
        $this->assertNotNull($dto->rows[0]->values['scheduled_at']);
        $this->assertNotNull($dto->rows[0]->metadata['external_match_id']);
    }

    public function test_match_detail_boxscore_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
        $extractor = new MatchDetailBoxscoreExtractor();

        $result = $extractor->extract($html);

        $this->assertArrayHasKey('data', $result);
        $dto = $result['data'];

        $this->assertGreaterThan(0, count($dto->rows));

        // Kontrola mapování sloupců
        $this->assertArrayHasKey('pts', $dto->columns);

        // Kontrola metadat
        $this->assertArrayHasKey('header', $dto->metadata);
        $this->assertArrayHasKey('home_team', $dto->metadata['header']);
    }
}
