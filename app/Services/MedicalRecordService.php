<?php

namespace App\services;

use App\Models\MedicalRecord;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    use ServiceResponse;

    public function getAllMedicalRecords()
    {
        try {
            return MedicalRecord::with(['appointment.doctor.user', 'appointment.patient.user'])->latest()->paginate(10);
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
