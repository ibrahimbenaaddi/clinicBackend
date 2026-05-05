<?php

namespace App\Policies;

use App\Models\User;

class DoctorPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function store(User $user): bool
    {
        return $user->role === 'admin';
    }
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $doctorId): bool
    {
        return $user->role === 'admin' || $doctorId === $user->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->role === 'admin';
    }
}
