<?php

namespace App\Policies;

use App\Models\AppointmentSlot;
use App\Models\User;

class AppointmentSlotPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function index(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function show(User $user, AppointmentSlot $slot): bool
    {
        return $user->role === 'admin' || $slot->doctor_id === $user->user_id;
    }

    public function getAllByDoctor(User $user, int $doctorId)
    {
        return $user->user_id === $doctorId;
    }
}
