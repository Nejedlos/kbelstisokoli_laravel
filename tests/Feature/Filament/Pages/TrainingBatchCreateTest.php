<?php

namespace Tests\Feature\Filament\Pages;

use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Filament\Resources\Trainings\Pages\CreateTraining;
use Livewire\Livewire;

class TrainingBatchCreateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_can_create_batch_trainings()
    {
        $team = Team::create([
            'name' => 'U14 Test',
            'slug' => 'u14-test',
            'category' => 'youth',
        ]);
        
        // Před testem by mělo být 0 tréninků (nebo aktuální počet)
        $initialCount = Training::count();
        
        Livewire::test(CreateTraining::class)
            ->set('data.teams', [$team->id])
            ->set('data.starts_at', now()->addDay()->format('Y-m-d H:i:s'))
            ->set('data.ends_at', now()->addDay()->addHour()->format('Y-m-d H:i:s'))
            ->set('data.repeat_frequency', 'weekly')
            ->set('data.repeat_count', 3)
            ->call('create')
            ->assertHasNoErrors();

        // Celkem by mělo být o 4 více tréninků
        $this->assertEquals($initialCount + 4, Training::count());
        
        // Poslední 4 tréninky by měly mít přiřazený tým
        $latestTrainings = Training::latest('id')->take(4)->get();
        foreach ($latestTrainings as $training) {
            $this->assertTrue($training->teams->contains($team->id));
        }
    }

    public function test_can_create_training_without_ends_at()
    {
        $team = Team::create([
            'name' => 'U14 Test 2',
            'slug' => 'u14-test-2',
            'category' => 'youth',
        ]);
        
        Livewire::test(CreateTraining::class)
            ->set('data.teams', [$team->id])
            ->set('data.starts_at', now()->addDay()->format('Y-m-d H:i:s'))
            ->set('data.ends_at', null)
            ->call('create')
            ->assertHasNoErrors();

        $training = Training::latest('id')->first();
        $this->assertNull($training->ends_at);
    }

    public function test_can_create_batch_trainings_by_period()
    {
        $team = Team::create([
            'name' => 'U14 Test 3',
            'slug' => 'u14-test-3',
            'category' => 'youth',
        ]);
        
        $initialCount = Training::count();
        
        // Start zítra
        $startsAt = now()->addDay()->startOfDay()->addHours(10); // 10:00
        
        Livewire::test(CreateTraining::class)
            ->set('data.teams', [$team->id])
            ->set('data.starts_at', $startsAt->format('Y-m-d H:i:s'))
            ->set('data.repeat_frequency', 'weekly')
            ->set('data.repeat_period', '1_month')
            ->call('create')
            ->assertHasNoErrors();

        $count = Training::count() - $initialCount;
        $this->assertGreaterThan(3, $count);
        $this->assertLessThan(7, $count);
    }
}
