<?php

namespace App\Policies;

use App\Models\ClubCompetition;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClubCompetitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_competitions') || $user->can('view_member_section');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClubCompetition $clubCompetition): bool
    {
        return $user->can('manage_competitions') || $user->can('view_member_section');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage_competitions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClubCompetition $clubCompetition): bool
    {
        return $user->can('manage_competitions');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClubCompetition $clubCompetition): bool
    {
        return $user->can('manage_competitions');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClubCompetition $clubCompetition): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClubCompetition $clubCompetition): bool
    {
        return false;
    }
}
