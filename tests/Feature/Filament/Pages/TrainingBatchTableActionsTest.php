<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Models\Team;
use App\Models\Training;
use Livewire\Livewire;
use Tests\TestCase;

class TrainingBatchTableActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->with2FA($this->createAdmin());
        $this->confirm2FA($user);
        $this->actingAs($user);
    }

    public function test_can_bulk_delete_trainings()
    {
        $trainings = Training::factory()->count(3)->create();

        Livewire::test(ListTrainings::class)
            ->callTableBulkAction('delete', $trainings)
            ->assertHasNoErrors();

        foreach ($trainings as $training) {
            $this->assertModelMissing($training);
        }
    }

    public function test_can_bulk_change_location()
    {

        $trainings = Training::factory()->count(3)->create([
            'location' => 'Old Hall',
        ]);

        Livewire::test(ListTrainings::class)
            ->callTableBulkAction('change_location', $trainings, [
                'new_location' => 'New Hall',
            ])
            ->assertHasNoErrors();

        foreach ($trainings as $training) {
            $this->assertEquals('New Hall', $training->fresh()->location);
        }
    }

    public function test_can_bulk_change_teams()
    {

        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $originalTeam = Team::factory()->create();
        $trainings = Training::factory()->count(2)->create();
        foreach ($trainings as $training) {
            $training->teams()->attach($originalTeam);
        }
        $untouched = Training::factory()->create();
        $untouched->teams()->attach($originalTeam);

        Livewire::test(ListTrainings::class)
            ->callTableBulkAction('change_teams', $trainings, [
                'teams' => [$team1->id, $team2->id],
            ])
            ->assertHasNoErrors();

        foreach ($trainings as $training) {
            $this->assertCount(2, $training->fresh()->teams);
            $this->assertTrue($training->fresh()->teams->contains($team1->id));
            $this->assertTrue($training->fresh()->teams->contains($team2->id));
        }
        $this->assertSame([$originalTeam->id], $untouched->fresh()->teams->modelKeys());
    }
}
