<?php

namespace App\Policies;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\User;

class AutoPolicy
{
    /**
     * Determine whether the user can view any autos.
     * Returns true if user has at least one status permission.
     */
    public function viewAny(User $user): bool
    {
        return count(Statuses::allowedFor($user)) > 0;
    }

    /**
     * Determine whether the user can view the auto.
     * Checks both company ownership and status permission.
     */
    public function view(User $user, Auto $auto): bool
    {
        // Check status permission first
        if (!$this->canViewStatus($user, $auto->status)) {
            return false;
        }

        return $user->hasRole('admin') || (int) $user->company_id === (int) $auto->company_id;
    }

    /**
     * Determine whether the user can update the auto (incl. transitions).
     */
    public function update(User $user, Auto $auto): bool
    {
        return $user->hasRole('admin') || (int) $user->company_id === (int) $auto->company_id;
    }

    /**
     * Check if user has permission to view autos with the given status.
     */
    public function canViewStatus(User $user, Statuses $status): bool
    {
        return $user->can($status->permissionName());
    }
}
