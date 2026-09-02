<?php

namespace Tests\Feature\Support;

use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\Season;
use App\Models\Team;
use App\Support\HeroEventsHelper;
use Tests\TestCase;

class HeroEventsHelperTest extends TestCase
{
    public function test_it_groups_events_for_multiple_teams()
    {
        // 1. Připravíme týmy
        $teamC = Team::create([
            'name' => ['cs' => 'Sokol Kbely C', 'en' => 'Sokol Kbely C'],
            'slug' => 'sokol-kbely-c',
        ]);
        $teamE = Team::create([
            'name' => ['cs' => 'Sokol Kbely E', 'en' => 'Sokol Kbely E'],
            'slug' => 'sokol-kbely-e',
        ]);

        // 2. Připravíme klubovou akci pro oba týmy
        $event = ClubEvent::create([
            'title' => ['cs' => 'Společný trénink', 'en' => 'Joint Training'],
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(1)->addHours(2),
            'is_public' => true,
            'event_type' => 'training',
        ]);
        $event->teams()->attach([$teamC->id, $teamE->id]);

        // 3. Získáme akce
        $upcomingEvents = HeroEventsHelper::getUpcomingEvents();

        // 4. Ověříme, že tam není dvakrát stejná akce
        $count = $upcomingEvents->where('id', $event->id)->where('type', 'event')->count();

        $this->assertEquals(1, $count, 'Akce pro více týmů by měla být v seznamu pouze jednou.');

        // 5. Ověříme, že team_short obsahuje oba týmy (např. "C & E")
        $formattedEvent = $upcomingEvents->where('id', $event->id)->where('type', 'event')->first();
        $this->assertStringContainsString('C', $formattedEvent['team_short']);
        $this->assertStringContainsString('E', $formattedEvent['team_short']);
    }

    public function test_it_groups_matches_for_multiple_teams()
    {
        // Potřebujeme sezónu
        $season = Season::create(['name' => '2025/2026', 'is_active' => true]);

        // 1. Připravíme týmy
        $teamC = Team::create([
            'name' => ['cs' => 'Sokol Kbely C', 'en' => 'Sokol Kbely C'],
            'slug' => 'sokol-kbely-c-match',
        ]);
        $teamE = Team::create([
            'name' => ['cs' => 'Sokol Kbely E', 'en' => 'Sokol Kbely E'],
            'slug' => 'sokol-kbely-e-match',
        ]);

        // 2. Připravíme zápas pro oba týmy
        $match = BasketballMatch::create([
            'scheduled_at' => now()->addDays(2),
            'status' => 'scheduled',
            'match_type' => 'league',
            'season_id' => $season->id,
        ]);
        $match->teams()->attach([$teamC->id, $teamE->id]);

        // 3. Získáme akce
        $upcomingEvents = HeroEventsHelper::getUpcomingEvents();

        // 4. Ověříme, že tam není dvakrát stejný zápas
        $count = $upcomingEvents->where('id', $match->id)->where('type', 'match')->count();
        $this->assertEquals(1, $count, 'Zápas pro více týmů by měl být v seznamu pouze jednou.');
    }
}
