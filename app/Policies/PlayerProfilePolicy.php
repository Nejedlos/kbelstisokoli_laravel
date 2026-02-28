<?php

namespace App\Policies;

use App\Models\PlayerProfile;
use App\Models\User;

class PlayerProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users') || $user->can('manage_teams') || $user->can('view_member_section');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlayerProfile $playerProfile): bool
    {
        return $user->can('manage_users')
            || $user->can('manage_teams')
            || ($user->id === $playerProfile->user_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage_users') || $user->can('manage_teams');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlayerProfile $playerProfile): bool
    {
        return $user->can('manage_users') || $user->can('manage_teams');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlayerProfile $playerProfile): bool
    {
        return $user->can('manage_users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlayerProfile $playerProfile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlayerProfile $playerProfile): bool
    {
        return false;
    }
}
