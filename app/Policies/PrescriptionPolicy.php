<?php

namespace App\Policies;

use App\Models\User;

class PrescriptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function index(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function store(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'doctor';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'doctor';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'doctor';
    }

    public function getAllByDoctor(User $user, int $doctorId)
    {
        return $user->user_id === $doctorId;
    }

    public function getAllByPatient(User $user, int $patientId)
    {
        return $user->user_id === $patientId;
    }
}