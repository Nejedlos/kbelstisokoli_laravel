<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Resources\ExternalTeamSeasonConfigs\Pages\EditExternalTeamSeasonConfig;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use Livewire\Livewire;
use Tests\TestCase;

class ExternalTeamSeasonConfigFormTest extends TestCase
{
    public function test_admin_can_enable_standings_only_and_preserve_other_metadata(): void
    {
        $this->actingAs($this->createAdmin());
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $team = Team::factory()->create();
        $config = ExternalTeamSeasonConfig::create([
            'source_key' => 'czbasketball',
            'season_id' => $season->id,
            'team_id' => $team->id,
            'external_team_id' => '7761',
            'external_season_year' => 2026,
            'team_season_url' => 'https://example.test/team',
            'matches_list_url' => 'https://example.test/matches',
            'is_enabled' => false,
            'metadata' => [
                'standings_enabled' => false,
                'existing_key' => 'preserved',
            ],
        ]);
        $competitionUrl = 'https://pbasket.cz.basketball/soutez/5379?p=10466';

        Livewire::test(EditExternalTeamSeasonConfig::class, ['record' => $config->getRouteKey()])
            ->assertFormSet([
                'metadata.standings_enabled' => false,
            ])
            ->fillForm([
                'competition_url' => $competitionUrl,
                'metadata.standings_enabled' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $config->refresh();

        $this->assertFalse($config->is_enabled);
        $this->assertSame($competitionUrl, $config->competition_url);
        $this->assertTrue($config->metadata['standings_enabled']);
        $this->assertSame('preserved', $config->metadata['existing_key']);
    }
}
