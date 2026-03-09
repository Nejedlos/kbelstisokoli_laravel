<?php

namespace Tests\Unit\Prediction;

use App\Services\Prediction\EloCalculator;
use PHPUnit\Framework\TestCase;

class EloCalculatorTest extends TestCase
{
    private EloCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new EloCalculator();
    }

    public function test_expected_probability_is_50_for_equal_ratings_without_home_adv(): void
    {
        $prob = $this->calculator->calculateExpected(1500, 1500, false);
        $this->assertEquals(0.5, $prob);
    }

    public function test_expected_probability_is_higher_with_home_advantage(): void
    {
        $probHome = $this->calculator->calculateExpected(1500, 1500, true);
        $probAway = $this->calculator->calculateExpected(1500, 1500, false);

        $this->assertGreaterThan($probAway, $probHome);
    }

    public function test_rating_change_is_positive_on_win(): void
    {
        $result = $this->calculator->calculateNewRatings(1500, 1500, 1, 10, false);
        $this->assertGreaterThan(1500, $result['new_elo_team']);
        $this->assertLessThan(1500, $result['new_elo_opponent']);
    }

    public function test_rating_change_is_negative_on_loss(): void
    {
        $result = $this->calculator->calculateNewRatings(1500, 1500, 0, -10, false);
        $this->assertLessThan(1500, $result['new_elo_team']);
        $this->assertGreaterThan(1500, $result['new_elo_opponent']);
    }

    public function test_margin_of_victory_affects_elo_change(): void
    {
        $resSmall = $this->calculator->calculateNewRatings(1500, 1500, 1, 1, false);
        $resLarge = $this->calculator->calculateNewRatings(1500, 1500, 1, 30, false);

        $this->assertGreaterThan($resSmall['new_elo_team'], $resLarge['new_elo_team']);
    }
}
