<?php

namespace App\Services;

use App\Models\Appointment;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    use ServiceResponse;

    public function getAllAppointments()
    {
        try {
            return Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->latest()->paginate(10);
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
    public function getAllByPatient(int $patientId)
    {
        try {
            return Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('patient_id', $patientId)->latest()->paginate(10);
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
    public function getAllByDoctor(int $doctorId)
    {
        try {
            return Appointment::with(['doctor.user', 'patient.user', 'record', 'invoices', 'record.prescriptions'])->where('doctor_id', $doctorId)->latest()->paginate(10);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'AppointmentService', $e);
        }
    }

    public function updateStatus(int $doctorId, int $appointmentId, array $credentials){
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
