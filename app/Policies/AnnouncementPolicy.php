<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_content');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('manage_content');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_content');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('manage_content');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('manage_content');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Announcement $announcement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return false;
    }
}
