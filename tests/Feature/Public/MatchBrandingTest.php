<?php

namespace Tests\Feature\Public;

use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Services\BrandingService;
use App\Services\TeamBrandingResolver;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MatchBrandingTest extends TestCase
{
    #[DataProvider('internalMatches')]
    public function test_internal_match_uses_the_website_logo_on_the_correct_side(string $slug, bool $isHome, bool $legacy): void
    {
        $team = Team::factory()->create(['slug' => $slug]);
        $match = $this->createMatch($isHome, $legacy ? $team : null);
        if (! $legacy) {
            $match->teams()->attach($team);
        }

        $settings = app(BrandingService::class)->getSettings();
        $resolver = app(TeamBrandingResolver::class);
        $logo = $resolver->getMatchLogo($match, $isHome);

        $this->assertSame(web_asset($settings['team_logo']['paths']['mini'], false), $logo['logo_url']);
        $this->assertSame(web_asset($settings['team_logo']['paths']['mini'], true), $logo['logo_url_webp']);
        $this->assertNull($resolver->getMatchLogo($match, ! $isHome));

        $this->blade('<x-match-card :match="$match" />', ['match' => $match])
            ->assertSee($logo['logo_url'], false)
            ->assertSee($logo['logo_url_webp'], false)
            ->assertDontSee('tj_sokol_kbely_basketball_logo', false);

        $response = $this->get(route('public.matches.show', $match->id))->assertOk();
        $document = new DOMDocument;
        @$document->loadHTML($response->getContent());
        $images = (new DOMXPath($document))->query('//main//img[contains(@style, "height: 56px")]');
        $this->assertCount(1, $images);
        $this->assertSame(web_asset($settings['team_logo']['paths']['velke'], false), $images->item(0)->getAttribute('src'));
    }

    public static function internalMatches(): array
    {
        return [
            'C home pivot' => ['muzi-c', true, false],
            'C away pivot' => ['muzi-c', false, false],
            'E home pivot' => ['muzi-e', true, false],
            'E away pivot' => ['muzi-e', false, false],
            'C home legacy' => ['muzi-c', true, true],
            'C away legacy' => ['muzi-c', false, true],
            'E home legacy' => ['muzi-e', true, true],
            'E away legacy' => ['muzi-e', false, true],
        ];
    }

    public function test_both_internal_teams_share_the_logo_for_a_joint_match(): void
    {
        $match = $this->createMatch(true);
        foreach (['muzi-c', 'muzi-e'] as $slug) {
            $match->teams()->attach(Team::factory()->create(['slug' => $slug]));
        }

        $this->assertTrue(app(TeamBrandingResolver::class)->getMatchLogo($match)['is_internal']);
    }

    public function test_other_team_keeps_the_parent_logo(): void
    {
        $match = $this->createMatch(true);
        $match->teams()->attach(Team::factory()->create(['slug' => 'muzi-b']));

        $this->assertFalse(app(TeamBrandingResolver::class)->getMatchLogo($match)['is_internal']);
    }

    private function createMatch(bool $isHome, ?Team $legacyTeam = null): BasketballMatch
    {
        return BasketballMatch::create([
            'team_id' => $legacyTeam?->id,
            'season_id' => Season::create(['name' => '2026/2027', 'is_active' => true])->id,
            'opponent_id' => Opponent::create(['name' => 'Testovací soupeř'])->id,
            'scheduled_at' => now()->addWeek(),
            'is_home' => $isHome,
            'status' => 'planned',
        ]);
    }
}
