<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
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
    public function show(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin' || $appointment->doctor_id === $user->user_id || $appointment->patient_id === $user->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function store(User $user): bool
    {
        return $user->role === 'patient' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function destroy(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function getAllByPatient(User $user, int $patientId): bool
    {
        return $user->user_id === $patientId || $user->role === 'doctor';
    }

    public function cancelAppointment(User $user, int $patientId): bool
    {
        return $user->user_id === $patientId;
    }

    public function getAllByDoctor(User $user, int $doctorId): bool
    {
        return $user->user_id === $doctorId;
    }

    public function updateStatus(User $user, int $doctorId): bool
    {
        return $user->user_id === $doctorId;
    }
}
