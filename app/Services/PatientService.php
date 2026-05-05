<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    use ServiceResponse, Searchable;

    public function getAllPatients(Request $request)
    {
        try {

            $query = Patient::query()->with('user');
            if ($request->filled('search')) {
                $term = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('phone', 'like', $term)
                        ->orWhere('insurance_info', 'like', $term)
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('firstname', 'like', $term)
                                ->orWhere('lastname',  'like', $term);
                        });
                });
            }
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
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
            return Patient::with(['user', 'appointments.record.prescriptions', 'appointments.invoices'])->findOrFail($patientId);
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

            $isDeleted = $patient->delete();
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