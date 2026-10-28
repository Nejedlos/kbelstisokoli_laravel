<?php

namespace Tests\Feature\Prediction;

use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Team;
use Tests\TestCase;

class PredictionModalSidesTest extends TestCase
{
    public function test_modal_pairs_comparison_players_and_recent_results_with_their_teams_at_home_and_away(): void
    {
        foreach ([true, false] as $isHome) {
            $ourSide = $isHome ? 'home' : 'away';
            $theirSide = $isHome ? 'away' : 'home';
            $match = new BasketballMatch([
                'is_home' => $isHome,
                'metadata' => [
                    'official_team_name' => 'Sokol Kbely C',
                    'official_opponent_name' => 'Opponents Official',
                    'best_players' => ['points' => ['label' => 'Points', $ourSide => ['name' => 'Our Player', 'value' => 11], $theirSide => ['name' => 'Their Player', 'value' => 22]]],
                    'team_comparison' => ['rating' => ['label' => 'Rating', $ourSide => '111', $theirSide => '222']],
                    'last_matches' => [
                        $ourSide => [
                            ['team_home' => 'Sokol Kbely C', 'team_away' => 'Other Club', 'score_home' => 90, 'score_away' => 60],
                            ['team_home' => 'Unknown Home', 'team_away' => 'Unknown Away', 'score_home' => 80, 'score_away' => 70],
                        ],
                        $theirSide => [['team_home' => 'Opponents Official', 'team_away' => 'Another Club', 'score_home' => 50, 'score_away' => 80]],
                    ],
                ],
            ]);
            $match->setRelation('team', new Team(['name' => 'Local Team']));
            $match->setRelation('opponent', new Opponent(['name' => 'Local Opponent']));
            $match->setRelation('prediction', null);

            $html = view('components.member.match-prediction-modal', ['match' => $match])->render();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
            $xpath = new \DOMXPath($dom);

            $players = $xpath->query('//span[contains(@class,"truncate") and (normalize-space()="Our Player" or normalize-space()="Their Player")]/parent::div');
            $this->assertCount(2, $players);
            $this->assertStringContainsString('Local Team', $players->item(0)->textContent);
            $this->assertStringContainsString('Our Player', $players->item(0)->textContent);
            $this->assertStringContainsString('Local Opponent', $players->item(1)->textContent);
            $this->assertStringContainsString('Their Player', $players->item(1)->textContent);

            $values = $xpath->query('//span[contains(@class,"tabular-nums") and (normalize-space()="111" or normalize-space()="222")]');
            $this->assertSame('111', trim($values->item(0)->textContent));
            $this->assertSame('222', trim($values->item(1)->textContent));

            $ourResult = $xpath->query('//div[@title="Sokol Kbely C vs Other Club (90:60)"]')->item(0);
            $theirResult = $xpath->query('//div[@title="Opponents Official vs Another Club (50:80)"]')->item(0);
            $this->assertSame('V', trim($ourResult->textContent));
            $this->assertSame('P', trim($theirResult->textContent));
            $this->assertStringContainsString('Other Club', $html);
            $this->assertStringContainsString('Another Club', $html);
            $this->assertStringContainsString('Unknown Home vs Unknown Away', $html);
            $unknownResult = $xpath->query('//div[@title="Unknown Home vs Unknown Away (80:70)"]')->item(0);
            $this->assertSame('', trim($unknownResult->textContent));
        }
    }
}
