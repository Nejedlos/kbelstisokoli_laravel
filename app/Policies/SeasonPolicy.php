<?php

namespace App\Policies;

use App\Models\Season;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SeasonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('access_admin');
    }

    public function view(User $user, Season $season): bool
    {
        return $user->can('access_admin');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function update(User $user, Season $season): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function delete(User $user, Season $season): bool
    {
        return $user->can('manage_advanced_settings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Season $season): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Season $season): bool
    {
        return false;
    }
}
