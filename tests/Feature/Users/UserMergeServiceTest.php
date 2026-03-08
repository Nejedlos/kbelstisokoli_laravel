<?php

namespace Tests\Feature\Users;

use App\Models\User;
use App\Services\Users\UserMergeService;
use App\Services\Stats\Sync\StatisticSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserMergeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserMergeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking StatisticSyncService to avoid deep dependencies
        $mock = $this->createMock(StatisticSyncService::class);
        $this->service = new UserMergeService($mock);
    }

    public function test_merging_users_with_conflicting_relationships()
    {
        // 1. Vytvoříme cílového uživatele (target)
        $target = User::factory()->create(['name' => 'Target User', 'email' => 'target@example.com']);

        // 2. Vytvoříme zdrojového uživatele (source)
        $source = User::factory()->create(['name' => 'Source Ghost', 'email' => 'ghost_123@example.com']);

        // 3. Vytvoříme společné dítě
        $child = User::factory()->create(['name' => 'Common Child']);

        // Vazba Target -> Child
        DB::table('user_relationships')->insert([
            'parent_id' => $target->id,
            'child_id' => $child->id,
            'relationship_type' => 'father',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vazba Source -> Child (toto by způsobilo konflikt při updatu v MySQL)
        DB::table('user_relationships')->insert([
            'parent_id' => $source->id,
            'child_id' => $child->id,
            'relationship_type' => 'mother',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Vytvoříme unikátní dítě pro Source
        $sourceOnlyChild = User::factory()->create(['name' => 'Source Only Child']);
        DB::table('user_relationships')->insert([
            'parent_id' => $source->id,
            'child_id' => $sourceOnlyChild->id,
            'relationship_type' => 'parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Spustíme merge
        $this->service->merge($source, $target);

        // 6. Ověříme výsledky
        // Zdrojový uživatel by měl být smazán
        $this->assertDatabaseMissing('users', ['id' => $source->id]);

        // Cílový uživatel by měl mít obě děti (jedno zděděné, jedno původní)
        $this->assertEquals(2, $target->children()->count());

        $childrenIds = $target->children->pluck('id');
        $this->assertContains($child->id, $childrenIds);
        $this->assertContains($sourceOnlyChild->id, $childrenIds);

        // Původní vazba Target -> Child by měla zůstat (relationship_type 'father')
        $this->assertDatabaseHas('user_relationships', [
            'parent_id' => $target->id,
            'child_id' => $child->id,
            'relationship_type' => 'father',
        ]);

        // Vazba Source -> Child by měla být smazána (buď cascádou nebo přes sloučení)
        $this->assertDatabaseMissing('user_relationships', [
            'parent_id' => $source->id,
            'child_id' => $child->id,
        ]);
    }

    public function test_merging_users_where_source_is_child()
    {
        // Target (v budoucnu dítě)
        $target = User::factory()->create(['name' => 'Target Child', 'email' => 'target@example.com']);

        // Source (v budoucnu dítě, které zanikne)
        $source = User::factory()->create(['name' => 'Source Ghost', 'email' => 'ghost_123@example.com']);

        // Rodič, který má obě "děti"
        $parent = User::factory()->create(['name' => 'Common Parent']);

        // Vazba Parent -> Target
        DB::table('user_relationships')->insert([
            'parent_id' => $parent->id,
            'child_id' => $target->id,
            'relationship_type' => 'guardian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vazba Parent -> Source
        DB::table('user_relationships')->insert([
            'parent_id' => $parent->id,
            'child_id' => $source->id,
            'relationship_type' => 'father',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Spustíme merge
        $this->service->merge($source, $target);

        // Ověříme
        $this->assertDatabaseMissing('users', ['id' => $source->id]);
        $this->assertEquals(1, $target->parents()->count());
        $this->assertEquals($parent->id, $target->parents->first()->id);

        // Měla by zůstat ta původní vazba k Targetu
        $this->assertDatabaseHas('user_relationships', [
            'parent_id' => $parent->id,
            'child_id' => $target->id,
            'relationship_type' => 'guardian',
        ]);
    }
}
