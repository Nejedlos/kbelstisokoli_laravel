<?php

namespace Tests\Feature\Livewire\Member;

use App\Livewire\Member\EventCard;
use App\Models\Training;
use Livewire\Livewire;
use Tests\TestCase;

class EventCardRefreshTest extends TestCase
{
    public function test_cards_only_listen_for_their_own_event_and_use_isolated_requests(): void
    {
        $this->actingAs($this->createMember());

        // More cards than Livewire's default request limit must remain independent.
        foreach (range(1, 21) as $index) {
            $match = Training::factory()->create(['starts_at' => now()->addWeek()]);
            $card = Livewire::test(EventCard::class, [
                'event' => ['type' => 'training', 'data' => $match],
            ]);

            $this->assertSame(["attendanceUpdated.training.{$match->id}"], $card->effects['listeners']);
            $this->assertTrue($card->snapshot['memo']['isolate']);
        }
    }

    public function test_status_change_saves_and_renders_without_broadcasting_to_other_cards(): void
    {
        $member = $this->createMember();
        $this->actingAs($member);
        $match = Training::factory()->create(['starts_at' => now()->addWeek()]);

        Livewire::test(EventCard::class, ['event' => ['type' => 'training', 'data' => $match]])
            ->call('setStatus', 'confirmed')
            ->assertHasNoErrors()
            ->assertNotDispatched('attendanceUpdated')
            ->dispatch("attendanceUpdated.training.{$match->id}")
            ->assertOk();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $member->id,
            'attendable_type' => Training::class,
            'attendable_id' => $match->id,
            'planned_status' => 'confirmed',
        ]);
    }
}
