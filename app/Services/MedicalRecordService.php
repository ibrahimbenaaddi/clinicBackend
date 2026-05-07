<?php

namespace App\services;

use App\Models\MedicalRecord;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    use ServiceResponse, Searchable;

    public function getAllMedicalRecords(Request $request)
    {
        try {
            $query = MedicalRecord::query()->with(['appointment.doctor.user', 'appointment.patient.user']);
            $query = $this->search($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllMedicalRecords', 'MedicalRecordService', $e);
        }
    }

    public function createMedicalRecord(array $credentials)
    {
        try {
            DB::beginTransaction();

            $medicalRecord = MedicalRecord::create($credentials);
            if (blank($medicalRecord)) {
                DB::rollBack();
                return self::theLog('createMedicalRecord', 'MedicalRecordService', new Exception('The medical recod is not created'));
            }

            $medicalRecord->load(['appointment.doctor.user', 'appointment.patient.user']);

            DB::commit();
            return $medicalRecord;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('createMedicalRecord', 'MedicalRecordService', $e);
        }
    }

    public function getMedicalRecord(int $recordId)
    {
        try {
            return MedicalRecord::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($recordId);
        } catch (Exception $e) {
            return self::theLog('getMedicalRecord', 'MedicalRecordService', $e);
        }
    }

    public function updateMedicalRecord(array $credentials, int $recordId)
    {
        try {
            DB::beginTransaction();

            $record = MedicalRecord::with('appointment')->findOrFail($recordId);

            $isUpdated = $record->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateMedicalRecord', 'MedicalRecordService', new Exception('The medical record is not updated'));
            }

            $record->refresh();
            $record->load(['appointment.doctor.user', 'appointment.patient.user']);

            DB::commit();
            return $record;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateMedicalRecord', 'MedicalRecordService', $e);
        }
    }

    public function deleteMedicalRecord(int $recordId)
    {
        try {
            DB::beginTransaction();

            $record = MedicalRecord::with('appointment')->findOrFail($recordId);

            $isDeleted = $record->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteMedicalRecord', 'MedicalRecordService', new Exception('The medical record is not deleted'));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteMedicalRecord', 'MedicalRecordService', $e);
        }
    }

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = MedicalRecord::query()->with(['appointment.doctor.user', 'appointment.patient.user'])
                ->whereHas('appointment.doctor', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            $query = $this->search($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'MedicalRecordService', $e);
        }
    }

    public function getAllByPatient(Request $request, int $patientId)
    {
        try {
            $query = MedicalRecord::query()->with(['appointment.doctor.user', 'appointment.patient.user'])
                ->whereHas('appointment.patient', function ($q) use ($patientId) {
                    $q->where('patient_id', $patientId);
                });
            $query = $this->search($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByPatient', 'MedicalRecordService', $e);
        }
    }

    private function search(Builder $query, Request $request): Builder
    {
        if ($request->filled('diagnosis_code')) {
            $query->where('diagnosis_code', 'like', '%' . $request->query('diagnosis_code') . '%');
        }
        if ($request->filled('search')) {
            $term = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('clinical_notes', 'like', $term)
                    ->orWhere('symptoms', 'like', $term)
                    ->orWhereHas('appointment.doctor.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname', 'like', $term);
                    })
                    ->orWhereHas('appointment.patient.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname', 'like', $term);
                    });
            });
        }
        return $query;
    }
}
