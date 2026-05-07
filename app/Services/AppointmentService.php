<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
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
            $query = Appointment::query()->with(['doctor.user', 'patient.user']);
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

            $slot = AppointmentSlot::with('doctor')->where('slot_id', $credentials['slot_id'])
                ->lockForUpdate()
                ->first();;

            if (blank($slot) || $slot->status !== 'available' || $slot->doctor_id != $credentials['doctor_id']) {
                DB::rollBack();
                return self::theLog('createAppointment', 'AppointmentService', new Exception('The slot is invalid or no longer available, or belongs to another doctor.'));
            }

            $credentials['start_time'] = $slot->start_time;
            $credentials['end_time'] = $slot->end_time;
            $appointment = Appointment::create($credentials);
            if (blank($appointment)) {
                DB::rollBack();
                return self::theLog('createAppointment', 'AppointmentService', new Exception('The appointment is not created'));
            }

            $this->updateBookedCount($slot, 'createAppointment');
            $appointment->load(['doctor.user', 'patient.user']);

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
            return Appointment::with(['doctor.user', 'patient.user'])->findOrFail($appointmentId);
        } catch (Exception $e) {
            return self::theLog('getAppointment', 'AppointmentService', $e);
        }
    }

    public function updateAppointment(array $credentials, int $appointmentId)
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::with('appointmentSlot')->findOrFail($appointmentId);
            $oldSlot = $appointment->appointmentSlot;
            if (array_key_exists('slot_id', $credentials) && array_key_exists('doctor_id', $credentials)) {
                if ($oldSlot->slot_id == $credentials['slot_id']) {
                    unset($credentials['slot_id']);
                } else {
                    $newSlot = AppointmentSlot::with('doctor')->where('slot_id', $credentials['slot_id'])
                        ->lockForUpdate()
                        ->first();
                    if (blank($newSlot) || $newSlot->status !== 'available' || $newSlot->doctor_id != $credentials['doctor_id']) {
                        DB::rollBack();
                        return self::theLog('updateAppointment', 'AppointmentService', new Exception('The slot is invalid or no longer available, or belongs to another doctor.'));
                    }
                    $credentials['start_time'] = $newSlot->start_time;
                    $credentials['end_time'] = $newSlot->end_time;
                    $this->makeSlotAvailable($oldSlot, 'updateAppointment');
                    $this->updateBookedCount($newSlot, 'updateAppointment');
                }
            } else {
                unset($credentials['slot_id']);
            }

            $isUpdated = $appointment->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateAppointment', 'AppointmentService', new Exception('The Appointment is not updated'));
            }

            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user']);

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

            $appointment = Appointment::with('appointmentSlot')->findOrFail($appointmentId);

            if ($appointment->appointmentSlot) {
                $this->makeSlotAvailable($appointment->appointmentSlot, 'deleteAppointment');
            }
            $isDeleted = $appointment->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteAppointment', 'AppointmentService', new Exception('The Appointment is not deleted'));
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
            $query = Appointment::query()->with(['doctor.user', 'patient.user'])->where('patient_id', $patientId);
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

            $appointment = Appointment::with('appointmentSlot')->where('patient_id', $patientId)->findOrFail($appointmentId);

            if ($appointment->status === 'cancelled') {
                return self::theLog('cancelAppointment', 'AppointmentService', new Exception('The appointment is already cancelled'));
            }
            $isUpdated = $appointment->update([
                'status' => 'cancelled'
            ]);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('cancelAppointment', 'AppointmentService', new Exception('The appointment is not updated'));
            }

            $this->makeSlotAvailable($appointment->appointmentSlot, 'cancelAppointment');
            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user']);

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
            $query = Appointment::query()->with(['doctor.user', 'patient.user'])->where('doctor_id', $doctorId);
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

            $appointment = Appointment::with('appointmentSlot')->where('doctor_id', $doctorId)->findOrFail($appointmentId);

            if ($credentials['status'] && $credentials['status'] === 'cancelled') {
                $this->makeSlotAvailable($appointment->appointmentSlot, 'updateStatus');
            }
            $isUpdated = $appointment->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateStatus', 'AppointmentService', new Exception('The appointment status is not updated'));
            }

            $appointment->refresh();
            $appointment->load(['doctor.user', 'patient.user']);

            DB::commit();
            return $appointment;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateStatus', 'AppointmentService', $e);
        }
    }

    private function makeSlotAvailable(AppointmentSlot $slot, string $functionName)
    {
        $isUpdated = $slot->update([
            'booked_count' => $slot->booked_count == 0 ? 0 : $slot->booked_count - 1,
            'status' => "available"
        ]);

        if (!$isUpdated) {
            DB::rollBack();
            return self::theLog($functionName, 'AppointmentService', new Exception('The slot is not updated'));
        }
    }

    private function updateBookedCount(AppointmentSlot $slot, string $functionName)
    {
        $isUpdated = $slot->update([
            'booked_count' => $slot->booked_count + 1  ,
        ]);

        if (!$isUpdated) {
            DB::rollBack();
            return self::theLog($functionName, 'AppointmentService', new Exception('The slot is not updated'));
        }
    }
}
