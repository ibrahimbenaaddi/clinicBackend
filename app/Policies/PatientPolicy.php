<?php

namespace App\Policies;

use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function index(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'doctor';
    }
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
    public function update(User $user, int $patientId): bool
    {
        return $user->role === 'admin' || $patientId === $user->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->role === 'admin';
    }
}
