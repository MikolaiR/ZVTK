<?php

namespace App\Policies;

use App\Models\Auto;
use App\Models\User;

class AutoPolicy
{
    /**
     * Determine whether the user can view the auto.
     */
    public function view(User $user, Auto $auto): bool
    {
        return $user->hasRole('admin') || (int) $user->company_id === (int) $auto->company_id;
    }

    /**
     * Determine whether the user can update the auto (incl. transitions).
     */
    public function update(User $user, Auto $auto): bool
    {
        return $user->hasRole('admin') || (int) $user->company_id === (int) $auto->company_id;
    }
}
