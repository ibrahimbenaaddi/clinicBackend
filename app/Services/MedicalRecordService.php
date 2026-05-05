<?php

namespace App\services;

use App\Models\MedicalRecord;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    use ServiceResponse, Searchable;

    public function getAllMedicalRecords(Request $request)
    {
        try {
            $query = MedicalRecord::query()->with(['appointment.doctor.user', 'appointment.patient.user']);
            if ($request->filled('appointment_id')) {
                $query->where('appointment_id', (int) $request->query('appointment_id'));
            }
            if ($request->filled('diagnosis_code')) {
                $query->where('diagnosis_code', 'like', '%' . $request->query('diagnosis_code') . '%');
            }
            if ($request->filled('search')) {
                $term = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('clinical_notes', 'like', $term)
                        ->orWhere('symptoms', 'like', $term);
                });
            }
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
                return self::theLog('createMedicalRecord', 'MedicalRecordService');
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

            $record = MedicalRecord::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($recordId);

            $isUpdated = $record->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateMedicalRecord', 'MedicalRecordService');
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

            $record = MedicalRecord::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($recordId);

            $isDeleted = $record->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteMedicalRecord', 'MedicalRecordService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteMedicalRecord', 'MedicalRecordService', $e);
        }
    }
}
