<?php

namespace App\Services;

use App\Models\Appointment;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    use ServiceResponse, Searchable;

    private static array $validStatus = [
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'no_show'
    ];

    public function getAllAppointments(Request $request)
    {
        try {
            $query = Appointment::query()->with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions']);
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            if ($request->filled('search')) {
                $term = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($term) {
                    $q->WhereHas('doctor.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname',  'like', $term);
                    })
                        ->orWhereHas('patient.user', function ($uq) use ($term) {
                            $uq->where('firstname', 'like', $term)
                                ->orWhere('lastname',  'like', $term);
                        });
                });
            }
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllAppointments', 'AppointmentService', $e);
        }
    }

    public function createAppointment(array $credentials)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::create($credentials);
            if (blank($appointment)) {
                DB::rollBack();
                return self::theLog('createAppointment', 'AppointmentService');
            }

            $appointment->load(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions']);

            DB::commit();
            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('createAppointment', 'AppointmentService', $e);
        }
    }

    public function getAppointment(int $appointmentId)
    {
        try {
            return Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->findOrFail($appointmentId);
        } catch (Exception $e) {
            return self::theLog('getAppointment', 'AppointmentService', $e);
        }
    }

    public function updateAppointment(array $credentials, int $appointmentId)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->findOrFail($appointmentId);

            $isUpdated = $appointment->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateAppointment', 'AppointmentService');
            }

            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions']);

            DB::commit();
            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateAppointment', 'AppointmentService', $e);
        }
    }

    public function deleteAppointment(int $appointmentId): bool
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->findOrFail($appointmentId);

            $isDeleted = $appointment->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteAppointment', 'AppointmentService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteAppointment', 'AppointmentService', $e);
        }
    }

    // for Patient
    public function getAllByPatient(Request $request, int $patientId)
    {
        try {
            $query = Appointment::query()->with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('patient_id', $patientId);
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByPatient', 'AppointmentService', $e);
        }
    }

    public function cancelAppointment(int $patientId, int $appointmentId)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('patient_id', $patientId)->findOrFail($appointmentId);

            $isUpdated = $appointment->update([
                'status' => 'cancelled'
            ]);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('cancelAppointment', 'AppointmentService');
            }

            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions']);

            DB::commit();
            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('cancelAppointment', 'AppointmentService', $e);
        }
    }

    // for Doctor
    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = Appointment::query()->with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('doctor_id', $doctorId);
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            if ($request->filled('search')) {
                $term = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($term) {
                    $q->WhereHas('patient.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname',  'like', $term);
                    });
                });
            }
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'AppointmentService', $e);
        }
    }

    public function updateStatus(int $doctorId, int $appointmentId, array $credentials)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('doctor_id', $doctorId)->findOrFail($appointmentId);

            $isUpdated = $appointment->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('cancelAppointment', 'AppointmentService');
            }

            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions']);

            DB::commit();
            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('cancelAppointment', 'AppointmentService', $e);
        }
    }
}
