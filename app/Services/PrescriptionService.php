<?php

namespace App\Services;

use App\Models\Prescription;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    use ServiceResponse;

    public function getAllPrescription()
    {
        try {
            return Prescription::with(['record.appointment.doctor.user', 'record.appointment.patient.user'])->latest()->paginate(10);
        } catch (Exception $e) {
            return self::theLog('getAllPrescription', 'PrescriptionService', $e);
        }
    }

    public function createPrescription(array $credentials)
    {
        try {
            DB::beginTransaction();

            $prescription = Prescription::create($credentials);
            if (blank($prescription)) {
                DB::rollBack();
                return self::theLog('createPrescription', 'PrescriptionService');
            }

            $prescription->load(['record.appointment.doctor.user', 'record.appointment.patient.user']);
            DB::commit();
            return $prescription;
        } catch (Exception $e) {
            return self::theLog('createPrescription', 'PrescriptionService', $e);
        }
    }

    public function getPrescription(int $prescriptionId)
    {
        try {
            return Prescription::with(['record.appointment.doctor.user', 'record.appointment.patient.user'])->findOrFail($prescriptionId);
        } catch (Exception $e) {
            return self::theLog('getPrescription', 'PrescriptionService', $e);
        }
    }

    public function updatePrescription(array $credentials, int $prescriptionId)
    {
        try {
            DB::beginTransaction();

            $prescription = Prescription::with(['record.appointment.doctor.user', 'record.appointment.patient.user'])->findOrFail($prescriptionId);

            $isUpdated = $prescription->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updatePrescription', 'PrescriptionService');
            }

            $prescription->refresh();
            $prescription->load(['record.appointment.doctor.user', 'record.appointment.patient.user']);

            DB::commit();
            return $prescription;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updatePrescription', 'PrescriptionService', $e);
        }
    }

    public function deletePrescription(int $prescriptionId)
    {
        try {
            DB::beginTransaction();

            $prescription = Prescription::with(['record.appointment.doctor.user', 'record.appointment.patient.user'])->findOrFail($prescriptionId);

            $isDeleted = $prescription->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deletePrescription', 'PrescriptionService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deletePrescription', 'PrescriptionService', $e);
        }
    }
}
