<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Filament\Resources\BasketballMatches\Pages\CreateBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\EditBasketballMatch;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\OpponentQuickCreateService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BasketballMatchQuickOpponentTest extends TestCase
{
    private User $matchManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matchManager = User::factory()->create(['is_active' => true]);
        $this->matchManager->givePermissionTo(['access_admin', 'manage_matches']);
        $this->actingAs($this->matchManager);
    }

    #[Test]
    public function search_offers_literal_existing_results_and_one_create_option_for_a_new_full_name(): void
    {
        $service = app(OpponentQuickCreateService::class);
        $existing = Opponent::create(['name' => 'BK Kbely 100%_A']);
        $prefixMatch = Opponent::create(['name' => 'Tým sokolů']);

        $existingOptions = $service->searchOptions('  bk   kbely 100%_a  ');

        $this->assertSame('BK Kbely 100%_A', $existingOptions[$existing->id]);
        $this->assertCount(1, $existingOptions);

        $newOptions = $service->searchOptions('  Tým   sokolů   Malešice  ');
        $createOptions = collect($newOptions)
            ->filter(fn (string $label, int|string $value): bool => $service->isCreateOption($value));

        $this->assertArrayNotHasKey($prefixMatch->id, $newOptions);
        $this->assertCount(1, $createOptions);
        $this->assertSame('Vytvořit tým „Tým sokolů Malešice“', $createOptions->first());
        $this->assertSame([], $service->searchOptions('   '));
        $this->assertFalse(collect($service->searchOptions(str_repeat('a', 256)))
            ->keys()
            ->contains(fn (int|string $value): bool => $service->isCreateOption($value)));
    }

    #[Test]
    public function selecting_the_create_result_immediately_creates_and_selects_an_opponent_then_match_can_be_saved(): void
    {
        [$season, $team] = $this->createMatchDependencies();
        $service = app(OpponentQuickCreateService::class);
        $component = Livewire::test(CreateBasketballMatch::class);
        $opponentSelect = $component->instance()->getSchema('form')?->getComponentByStatePath('opponent_id');

        $this->assertInstanceOf(Select::class, $opponentSelect);
        $this->assertFalse($opponentSelect->isHtmlAllowed());

        $searchResults = $component->instance()->callSchemaComponentMethod(
            'form.opponent_id',
            'getSearchResultsForJs',
            ['search' => '  TJ   Sokol   Test  '],
        );
        $createResult = collect($searchResults)
            ->first(fn (array $option): bool => $service->isCreateOption($option['value']));

        $this->assertNotNull($createResult);
        $this->assertSame('Vytvořit tým „TJ Sokol Test“', $createResult['label']);

        $component->set('data.opponent_id', $createResult['value']);

        $opponent = Opponent::sole();
        $this->assertSame('TJ Sokol Test', $opponent->name);
        $this->assertNull($opponent->city);
        $this->assertNull($opponent->logo);
        $this->assertNull($opponent->metadata);
        $component->assertSet('data.opponent_id', (string) $opponent->id);

        $component
            ->set('data.teams', [$team->id])
            ->set('data.season_id', $season->id)
            ->set('data.scheduled_at', now()->addDay()->format('Y-m-d H:i:s'))
            ->call('create')
            ->assertHasNoErrors();

        $match = BasketballMatch::sole();
        $this->assertSame($opponent->id, $match->opponent_id);
        $this->assertTrue($match->teams->contains($team));
    }

    #[Test]
    public function edit_form_uses_the_same_immediate_create_flow_and_reuses_a_normalized_existing_name(): void
    {
        [$season, $team] = $this->createMatchDependencies();
        $originalOpponent = Opponent::create(['name' => 'Původní soupeř']);
        $normalizedExisting = Opponent::create(['name' => 'USK Praha']);
        $match = BasketballMatch::create([
            'match_type' => 'mistrovske',
            'season_id' => $season->id,
            'opponent_id' => $originalOpponent->id,
            'scheduled_at' => now()->addDay(),
            'is_home' => true,
            'status' => 'planned',
        ]);
        $match->teams()->attach($team);

        $service = app(OpponentQuickCreateService::class);
        Livewire::test(EditBasketballMatch::class, ['record' => $match->getRouteKey()])
            ->set('data.opponent_id', $service->createOptionValue('  usk   PRAHA '))
            ->assertSet('data.opponent_id', (string) $normalizedExisting->id);

        $this->assertSame(2, Opponent::count());
    }

    #[Test]
    public function immediate_creation_rechecks_match_authorization_and_rejects_forged_options(): void
    {
        $service = app(OpponentQuickCreateService::class);
        $component = Livewire::test(CreateBasketballMatch::class);

        $this->matchManager->revokePermissionTo('manage_matches');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $unauthorizedUser = $this->matchManager->fresh();
        auth()->setUser($unauthorizedUser);
        Filament::auth()->setUser($unauthorizedUser);
        $this->assertFalse($unauthorizedUser->can('manage_matches'));
        $this->assertFalse(BasketballMatchResource::canCreate());

        $component
            ->set('data.opponent_id', $service->createOptionValue('Bez oprávnění'))
            ->assertForbidden();
        $this->assertDatabaseMissing('opponents', ['name' => 'Bez oprávnění']);

        $this->matchManager->givePermissionTo('manage_matches');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $authorizedUser = $this->matchManager->fresh();
        auth()->setUser($authorizedUser);
        Filament::auth()->setUser($authorizedUser);

        $component = Livewire::test(CreateBasketballMatch::class);
        $component->set('data.opponent_id', OpponentQuickCreateService::CREATE_OPTION_PREFIX.'not!canonical');

        $component->assertSet('data.opponent_id', null);
        $component->assertHasErrors(['data.opponent_id']);
        $this->assertDatabaseCount('opponents', 0);
    }

    /**
     * @return array{Season, Team}
     */
    private function createMatchDependencies(): array
    {
        $season = Season::create(['name' => '2026/2027', 'is_active' => true]);
        $team = Team::factory()->create(['category' => 'youth']);

        return [$season, $team];
    }
}
