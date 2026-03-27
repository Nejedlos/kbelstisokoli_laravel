<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSeasonConfig;
use Illuminate\Auth\Access\Response;

class UserSeasonConfigPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function view(User $user, UserSeasonConfig $userSeasonConfig): bool
    {
        return $user->can('manage_users') || $user->id === $userSeasonConfig->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('manage_economy');
    }

    public function update(User $user, UserSeasonConfig $userSeasonConfig): bool
    {
        return $user->can('manage_economy');
    }

    public function delete(User $user, UserSeasonConfig $userSeasonConfig): bool
    {
        return $user->can('manage_economy');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserSeasonConfig $userSeasonConfig): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserSeasonConfig $userSeasonConfig): bool
    {
        return false;
    }
}
