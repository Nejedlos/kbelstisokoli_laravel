<?php

namespace Tests\Feature\Public;

use App\Models\BasketballMatch;
use App\Models\MatchPrediction;
use App\Models\Opponent;
use App\Models\Team;
use Illuminate\Support\Collection;
use Tests\TestCase;

class UndatedMatchCardTest extends TestCase
{
    public function test_undated_match_card_renders_localized_date_and_time_with_prediction(): void
    {
        $team = new Team(['name' => 'Sokol Kbely']);
        $match = new BasketballMatch(['season_id' => 4, 'is_home' => true, 'status' => 'planned']);
        $match->id = 1678;
        $match->setRelation('team', $team);
        $match->setRelation('teams', new Collection([$team]));
        $match->setRelation('opponent', new Opponent(['name' => 'Soupeř']));
        $match->setRelation('prediction', new MatchPrediction(['probability_win' => 0.73, 'confidence' => 'high']));

        foreach (['cs', 'en'] as $locale) {
            app()->setLocale($locale);
            $this->blade('<x-match-card :match="$match" />', ['match' => $match])
                ->assertSee(__('matches.date_not_specified'))
                ->assertSee(__('matches.time_not_specified'))
                ->assertSee('73%')
                ->assertSee('Sokol Kbely')
                ->assertSee('Soupeř');
        }
    }
}
