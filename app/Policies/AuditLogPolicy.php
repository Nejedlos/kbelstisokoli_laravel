<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->can('manage_advanced_settings');
    }

    public function create(User $user): bool
    {
        return false; // Logs are automated
    }

    public function update(User $user, AuditLog $auditLog): bool
    {
        return false; // Logs should be immutable
    }

    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole('admin'); // Only super-admin should delete logs
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuditLog $auditLog): bool
    {
        return false;
    }
}
