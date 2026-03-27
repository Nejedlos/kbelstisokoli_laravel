<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TrainingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_attendance');
    }

    public function view(User $user, Training $training): bool
    {
        return $user->can('manage_attendance');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_attendance');
    }

    public function update(User $user, Training $training): bool
    {
        return $user->can('manage_attendance');
    }

    public function delete(User $user, Training $training): bool
    {
        return $user->can('manage_attendance');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Training $training): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Training $training): bool
    {
        return false;
    }
}
