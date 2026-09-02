<?php

namespace Tests\Feature;

use App\Enums\MembershipType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MembershipRoleSynchronizationTest extends TestCase
{
    public function test_authentication_updates_preserve_legacy_and_privileged_roles(): void
    {
        $user = User::factory()->create(['membership_type' => null, 'membership_types' => null]);
        $user->syncRoles(['player', 'coach', 'admin', 'editor']);

        $user->forceFill([
            'last_login_at' => now(),
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->assertTrue($user->fresh()->hasAllRoles(['player', 'coach', 'admin', 'editor']));

        $user->update(['membership_types' => [MembershipType::Player->value]]);
        $this->assertTrue($user->fresh()->hasAllRoles(['player', 'admin', 'editor']));
        $this->assertFalse($user->fresh()->hasRole('coach'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['player', 'coach', 'parent', 'admin', 'editor'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    public function test_multiple_membership_types_assign_multiple_roles(): void
    {
        $user = User::factory()->create([
            'membership_types' => [MembershipType::Player->value, MembershipType::Coach->value],
        ]);

        $this->assertTrue($user->fresh()->hasAllRoles(['player', 'coach']));
        $this->assertSame(MembershipType::Player, $user->fresh()->membership_type);
    }

    public function test_removing_membership_type_removes_only_its_managed_role(): void
    {
        $user = User::factory()->create([
            'membership_types' => [MembershipType::Player->value, MembershipType::Coach->value],
        ]);
        $user->assignRole('admin');

        $user->update([
            'membership_types' => [MembershipType::Coach->value],
        ]);

        $user = $user->fresh();

        $this->assertFalse($user->hasRole('player'));
        $this->assertTrue($user->hasRole('coach'));
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_non_role_membership_preserves_manually_assigned_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('editor');

        $user->update([
            'membership_types' => [MembershipType::Honorary->value],
        ]);

        $user = $user->fresh();

        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasAnyRole(MembershipType::managedRoleNames()));
    }

    public function test_legacy_membership_type_is_available_as_normalized_membership(): void
    {
        $user = User::factory()->create([
            'membership_type' => MembershipType::Parent,
            'membership_types' => null,
        ]);

        $this->assertSame([MembershipType::Parent], $user->fresh()->getMembershipTypes());
        $this->assertTrue($user->fresh()->hasRole('parent'));
    }

    public function test_membership_types_migration_backfills_legacy_scalar_value(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'membership_types'));

        $id = DB::table('users')->insertGetId([
            'name' => 'Legacy Coach',
            'email' => 'legacy-coach@example.test',
            'password' => bcrypt('password'),
            'membership_type' => MembershipType::Coach->value,
            'membership_types' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_08_30_100000_add_membership_types_to_users_table.php');
        $migration->up();

        $this->assertSame(
            [MembershipType::Coach->value],
            json_decode(DB::table('users')->where('id', $id)->value('membership_types'), true, flags: JSON_THROW_ON_ERROR)
        );
    }

    public function test_membership_types_migration_infers_types_from_existing_roles(): void
    {
        $user = User::factory()->create([
            'membership_type' => null,
            'membership_types' => null,
        ]);
        $user->syncRoles(['player', 'coach']);

        $migration = require database_path('migrations/2026_08_30_100000_add_membership_types_to_users_table.php');
        $migration->up();

        $this->assertEqualsCanonicalizing(
            [MembershipType::Player->value, MembershipType::Coach->value],
            json_decode(DB::table('users')->where('id', $user->id)->value('membership_types'), true, flags: JSON_THROW_ON_ERROR)
        );
    }
}
