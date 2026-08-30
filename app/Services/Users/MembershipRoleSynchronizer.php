<?php

namespace App\Services\Users;

use App\Enums\MembershipType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MembershipRoleSynchronizer
{
    /**
     * Synchronize only roles derived from club membership.
     * Manually assigned roles such as admin, super_admin and editor are preserved.
     */
    public function sync(User $user): void
    {
        if (! $user->exists) {
            return;
        }

        $managedRoleNames = MembershipType::managedRoleNames();
        $desiredManagedRoles = collect($user->getMembershipTypes())
            ->map(fn (MembershipType $type) => $type->roleName())
            ->filter()
            ->unique()
            ->values();

        $preservedRoleNames = $user->roles()
            ->pluck('name')
            ->reject(fn (string $name) => in_array($name, $managedRoleNames, true));

        $desiredRoleNames = $preservedRoleNames
            ->merge($desiredManagedRoles)
            ->unique()
            ->values();

        $availableRoleNames = Role::query()
            ->whereIn('name', $desiredRoleNames)
            ->pluck('name');

        $missingRoleNames = $desiredRoleNames->diff($availableRoleNames);
        if ($missingRoleNames->isNotEmpty()) {
            Log::warning('Membership roles could not be assigned because they do not exist.', [
                'user_id' => $user->id,
                'roles' => $missingRoleNames->values()->all(),
            ]);
        }

        $user->syncRoles($availableRoleNames->all());
        $user->unsetRelation('roles');
    }
}
