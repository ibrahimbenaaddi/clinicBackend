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
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
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

            $prescription = Prescription::with('record')->findOrFail($prescriptionId);

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

            $prescription = Prescription::with('record')->findOrFail($prescriptionId);

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

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = Prescription::query()->with(['record.appointment.doctor.user', 'record.appointment.patient.user'])
                ->whereHas('record.appointment.doctor', function ($uq) use ($doctorId) {
                    $uq->where('doctor_id', $doctorId);
                });
            $query = $this->search($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllPrescription', 'PrescriptionService', $e);
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
