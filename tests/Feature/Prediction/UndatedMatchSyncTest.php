<?php

namespace Tests\Feature\Prediction;

use App\Jobs\Stats\SyncMatchDetailJob;
use App\Models\BasketballMatch;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UndatedMatchSyncTest extends TestCase
{
    public function test_undated_legacy_match_can_be_queued_for_synchronization(): void
    {
        Queue::fake();
        $team = Team::factory()->create(['slug' => 'undated-test']);
        $season = Season::create(['name' => '2026/2027']);
        $match = BasketballMatch::withoutEvents(fn () => BasketballMatch::create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent_id' => Opponent::create(['name' => 'Testovací soupeř'])->id,
            'scheduled_at' => null,
            'status' => 'planned',
            'metadata' => ['external_id' => 'undated-external-id'],
        ]));

        $this->artisan('stats:sync-match', [
            'matchExternalId' => 'undated-external-id',
            'seasonNameOrId' => $season->id,
            'teamSlug' => $team->slug,
            '--queue' => true,
        ])->expectsOutputToContain('Termín bude upřesněn')->assertSuccessful();
        Queue::assertPushed(SyncMatchDetailJob::class, 1);
        $this->assertNull($match->fresh()->scheduled_at);
    }
}
