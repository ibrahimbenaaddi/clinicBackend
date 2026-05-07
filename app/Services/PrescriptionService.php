<?php

namespace App\Services;

use App\Models\Prescription;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    use ServiceResponse, Searchable;

    public function getAllPrescription(Request $request)
    {
        try {
            $query = Prescription::query()->with(['record.appointment.doctor.user', 'record.appointment.patient.user']);
            $query = $this->search($query, $request);
            $page = self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage, ['*'], 'page', $page);
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
                return self::theLog('createPrescription', 'PrescriptionService', new Exception('The prescription is not created'));
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

            $prescription = Prescription::with('record')->findOrFail($prescriptionId);

            $isUpdated = $prescription->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updatePrescription', 'PrescriptionService', new Exception('The prescrioption is not updated'));
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

            $prescription = Prescription::with('record')->findOrFail($prescriptionId);

            $isDeleted = $prescription->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deletePrescription', 'PrescriptionService', new Exception('The prescription is not deleted'));
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deletePrescription', 'PrescriptionService', $e);
        }
    }

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = Prescription::query()->with(['record.appointment.doctor.user', 'record.appointment.patient.user'])
                ->whereHas('record.appointment.doctor', function ($uq) use ($doctorId) {
                    $uq->where('doctor_id', $doctorId);
                });
            $query = $this->search($query, $request);
            $page = self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage, ['*'], 'page', $page);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'PrescriptionService', $e);
        }
    }

    public function getAllByPatient(Request $request, int $patientId)
    {
        try {
            $query = Prescription::query()->with(['record.appointment.doctor.user', 'record.appointment.patient.user'])
                ->whereHas('record.appointment.patient', function ($uq) use ($patientId) {
                    $uq->where('patient_id', $patientId);
                });
            $query = $this->search($query, $request);
            $page = self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage, ['*'], 'page', $page);
        } catch (Exception $e) {
            return self::theLog('getAllByPatient', 'PrescriptionService', $e);
        }
    }

    private function search(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $term = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->WhereHas('record.appointment.doctor.user', function ($uq) use ($term) {
                    $uq->where('firstname', 'like', $term)
                        ->orWhere('lastname',  'like', $term);
                })
                    ->orWhereHas('record.appointment.patient.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname',  'like', $term);
                    });
            });
        }
        return $query;
    }
}
