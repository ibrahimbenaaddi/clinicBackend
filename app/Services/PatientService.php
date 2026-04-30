<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    use ServiceResponse;

    public function getAllPatients()
    {
        try {
            return Patient::with('user')->latest()->paginate(10);
        } catch (Exception $e) {
            return self::theLog('getAllPatients', 'PatientService', $e);
        }
    }

    public function createPatient(array $credentials)
    {
        try {
            DB::beginTransaction();

            $credentials['password'] = Hash::make($credentials['password']);

            $userData = Arr::only($credentials, ['firstname', 'lastname', 'email', 'password']);
            $userData['role'] = 'patient';
            $patientData = Arr::only($credentials, ['date_birth', 'address', 'phone', 'insurance_info']);

            $user = User::create($userData);
            if (blank($user)) {
                DB::rollBack();
                return self::theLog('createPatient', 'PatientService');
            }

            $patient = $user->patient()->create($patientData);
            if (blank($patient)) {
                DB::rollBack();
                return self::theLog('createPatient', 'PatientService');
            }

            $patient->load('user');

            DB::commit();
            return $patient;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('createPatient', 'PatientService', $e);
        }
    }

    public function getPatient(int $patientId)
    {
        try {
            return Patient::with('user')->findOrFail($patientId);
        } catch (Exception $e) {
            return self::theLog('getPatient', 'PatientService', $e);
        }
    }

    public function updatePatient(array $credentials, int $patientId)
    {
        try {
            DB::beginTransaction();

            $patient = Patient::with('user')->findOrFail($patientId);

            $userData = Arr::only($credentials, ['firstname', 'lastname']);
            $patientData = Arr::only($credentials, ['date_birth', 'address', 'phone', 'insurance_info']);

            if (!empty($userData)) {
                $isUpdated = $patient->user()->update($userData);
                if (!$isUpdated) {
                    DB::rollBack();
                    return self::theLog('updatePatient', 'PatientService');
                }
            }

            if (!empty($patientData)) {
                $isUpdated = $patient->update($patientData);
                if (!$isUpdated) {
                    DB::rollBack();
                    return self::theLog('updatePatient', 'PatientService');
                }
            }

            $patient->refresh();
            $patient->load('user');

            DB::commit();
            return $patient;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updatePatient', 'PatientService', $e);
        }
    }

    public function deletePatient(int $patientId): bool
    {
        try {
            DB::beginTransaction();

            $patient = Patient::with('user')->findOrFail($patientId);

            $isDeleted = $patient->user()->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deletePatient', 'PatientService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deletePatient', 'PatientService', $e);
        }
    }
}
