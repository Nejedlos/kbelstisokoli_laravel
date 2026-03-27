<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_teams');
    }

    public function view(User $user, Team $team): bool
    {
        return $user->can('manage_teams');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_teams');
    }

    public function update(User $user, Team $team): bool
    {
        return $user->can('manage_teams');
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->can('manage_teams');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Team $team): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Team $team): bool
    {
        return false;
    }
}
