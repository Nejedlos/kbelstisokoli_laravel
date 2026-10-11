<?php

namespace Tests\Feature\Stats\Extractors;

use App\Services\Stats\Extractors\CzBasketball\MatchDetailBoxscoreExtractor;
use App\Services\Stats\Extractors\CzBasketball\MatchesListExtractor;
use App\Services\Stats\Extractors\CzBasketball\TeamRosterExtractor;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CzBasketballExtractorTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_team_roster_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/team_page.html'));
        $extractor = new TeamRosterExtractor;

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
        $extractor = new MatchesListExtractor;

        $result = $extractor->extract($html);

        $this->assertArrayHasKey('data', $result);
        $dto = $result['data'];

        $this->assertGreaterThan(0, count($dto->rows));
        $this->assertNotNull($dto->rows[0]->values['scheduled_at']);
        $this->assertNotNull($dto->rows[0]->metadata['external_match_id']);
    }

    public function test_matches_list_extractor_reads_scored_pbasket_schedule_rows(): void
    {
        Carbon::setTestNow('2026-10-11 12:00:00');
        $html = <<<'HTML'
        <table class="table table-bordered table-striped">
            <thead><tr><th>kolo</th><th>datum a čas</th><th>domácí / hosté</th><th>skore</th><th>čtvrtiny</th><th>fáze sezony</th><td></td></tr></thead>
            <tbody><tr>
                <td>1. kolo</td><td>7. 10. 2026<br>20:15</td>
                <td><div class="text-nowrap">Sokol Kbely C</div><div class="text-nowrap">Sokol Kbely B</div></td>
                <td><a href="/zapas/549785"><div>47</div><div>54</div></a></td>
                <td></td><td>Základní fáze</td><td><a href="/zapas/549785">Review</a></td>
            </tr></tbody>
        </table>
        HTML;

        $rows = (new MatchesListExtractor)->extract($html)['data']->rows;

        $this->assertCount(1, $rows);
        $this->assertSame('549785', $rows[0]->values['external_match_id']);
        $this->assertSame('47:54', $rows[0]->values['score']);
        $this->assertSame('finished', $rows[0]->values['status']);
        $this->assertSame('2026-10-07 20:15:00', $rows[0]->values['scheduled_at']);
    }

    public function test_match_detail_boxscore_extractor()
    {
        $html = file_get_contents(base_path('tests/Fixtures/Stats/CzBasketball/match_detail.html'));
        $extractor = new MatchDetailBoxscoreExtractor;

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

    public function test_missing_boxscore_is_serializable_without_fabricated_rows(): void
    {
        $result = (new MatchDetailBoxscoreExtractor)->extract('<html><body><h1>Upcoming match</h1></body></html>');
        $this->assertSame([], $result['data']->rows);
        $this->assertSame([], $result['data']->toArray()['rows']);
        $this->assertContains('No tables found', $result['data']->warnings);
    }
}
