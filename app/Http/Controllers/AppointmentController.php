<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;

class AppointmentController extends Controller
{
    use ApiResponse, Helper;

    private AppointmentService $service;

    public function __construct()
    {
        $this->service = new AppointmentService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (! $appointments = $this->service->getAllAppointments()) {
                return self::failled('index', 'AppointmentController', 'read');
            };
            return self::readSuccess(AppointmentResource::collection($appointments));
        } catch (Exception $e) {
            return self::failled('index', 'AppointmentController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $appointment = $this->service->createAppointment($credentials)) {
                return self::failled('store', 'AppointmentController', 'create');
            }
            return self::createSuccess(new AppointmentResource($appointment));
        } catch (Exception $e) {
            return self::failled('store', 'AppointmentController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $appointmentId)
    {
        try {
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            if (! $appointment = $this->service->getAppointment($appointmentId)) {
                return self::failled('show', 'AppointmentController', 'read');
            };
            return self::readSuccess(new AppointmentResource($appointment));
        } catch (Exception $e) {
            return self::failled('show', 'AppointmentController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, int $appointmentId)
    {
        try {
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            $credentials = $request->validated();
            if (! $appointment = $this->service->updateAppointment($credentials, $appointmentId)) {
                return self::failled('update', 'AppointmentController', 'update');
            }
            return self::updateSuccess(new AppointmentResource($appointment));
        } catch (Exception $e) {
            return self::failled('update', 'AppointmentController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $appointmentId)
    {
        try {
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            if (! $this->service->deleteAppointment($appointmentId)) {
                return self::failled('delete', 'AppointmentController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'AppointmentController', 'delete', $e);
        }
    }

    // for Patient
    public function getAllByPatient(int $patientId)
    {
        try {
            self::validatorId($patientId, 'patient_id', 'patients');
            if (! $appointments = $this->service->getAllByPatient($patientId)) {
                return self::failled('getAllByPatient', 'AppointmentController', 'read');
            }
            return self::readSuccess(AppointmentResource::collection($appointments));
        } catch (Exception $e) {
            return self::failled('getAllByPatient', 'AppointmentController', 'read', $e);
        }
    }

    public function cancelAppointment(int $patientId, int $appointmentId)
    {
        try {
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            self::validatorId($patientId, 'patient_id', 'patients');
            if (! $appointment = $this->service->cancelAppointment($patientId, $appointmentId)) {
                return self::failled('cancelAppointment', 'AppointmentController', 'update');
            }
            return self::updateSuccess(new AppointmentResource($appointment));
        } catch (Exception $e) {
            return self::failled('cancelAppointment', 'AppointmentController', 'update', $e);
        }
    }

    // for Doctor
    public function getAllByDoctor(int $doctorId)
    {
        try {
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $appointments = $this->service->getAllByDoctor($doctorId)) {
                return self::failled('getAllByDoctor', 'AppointmentController', 'read');
            }
            return self::readSuccess(AppointmentResource::collection($appointments));
        } catch (Exception $e) {
            return self::failled('getAllByDoctor', 'AppointmentController', 'read', $e);
        }
    }

    public function updateStatus(UpdateStatusRequest $request, int $doctorId, int $appointmentId)
    {
        try {
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            $credentials = $request->validated();
            if (! $appointment = $this->service->updateStatus($doctorId, $appointmentId, $credentials)) {
                return self::failled('updateStatus', 'AppointmentController', 'update');
            }
            return self::updateSuccess(new AppointmentResource($appointment));
        } catch (Exception $e) {
            return self::failled('updateStatus', 'AppointmentController', 'update', $e);
        }
    }
}
