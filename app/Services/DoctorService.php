<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\User;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DoctorService
{
    use ServiceResponse;

    public function getAllDoctors()
    {
        try {
            return Doctor::with('user')->latest()->get();
        } catch (Exception $e) {
            return self::theLog('getAllDoctors', 'DoctorService', $e);
        }
    }

    public function createDoctor(array $credentials)
    {
        try {
            DB::beginTransaction();

            $credentials['password'] = Hash::make($credentials['password']);

            $userData = Arr::only($credentials, ['firstname', 'lastname', 'email', 'password']);
            $userData['role'] = 'doctor';
            $doctorData = Arr::only($credentials, ['specialization', 'license_number', 'phone']);

            $user = User::create($userData);
            if (blank($user)) {
                DB::rollBack();
                return self::theLog('createDoctor', 'DoctorService');
            }

            $doctor = $user->doctor()->create($doctorData);
            if (blank($doctor)) {
                DB::rollBack();
                return self::theLog('createDoctor', 'DoctorService');
            }

            $doctor->load('user');

            DB::commit();
            return $doctor;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('createDoctor', 'DoctorService', $e);
        }
    }

    public function getDoctor(int $doctorId)
    {
        try {
            return Doctor::with('user')->findOrFail($doctorId);
        } catch (Exception $e) {
            return self::theLog('getAllDoctors', 'DoctorService', $e);
        }
    }

    public function updateDoctor(array $credentials, int $doctorId)
    {
        try {
            DB::beginTransaction();

            $doctor = Doctor::with('user')->findOrFail($doctorId);

            $userData = Arr::only($credentials, ['firstname', 'lastname']);
            $doctorData = Arr::only($credentials, ['specialization', 'license_number', 'phone']);

            if (!empty($userData)) {
                $isUpdated = $doctor->user()->update($userData);
                if (!$isUpdated) {
                    DB::rollBack();
                    return self::theLog('updateDoctor', 'DoctorService');
                }
            }

            if (!empty($doctorData)) {
                $isUpdated = $doctor->update($doctorData);
                if (!$isUpdated) {
                    DB::rollBack();
                    return self::theLog('updateDoctor', 'DoctorService');
                }
            }

            $doctor->refresh();
            $doctor->load('user');

            DB::commit();
            return $doctor;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateDoctor', 'DoctorService', $e);
        }
    }

    public function deleteDoctor(int $doctorId): bool
    {
        try {
            DB::beginTransaction();

            $doctor = Doctor::with('user')->findOrFail($doctorId);
            
            $isDeleted = $doctor->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteDoctor', 'DoctorService');
            };

            $isDeleted = $doctor->user()->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteDoctor', 'DoctorService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteDoctor', 'DoctorService', $e);
        }
    }
}
