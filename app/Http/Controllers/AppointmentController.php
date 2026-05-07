<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueryParamRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
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
    public function index(QueryParamRequest $request)
    {
        try {
            $this->authorize('index', Appointment::class);
            if (! $appointments = $this->service->getAllAppointments($request)) {
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
            $this->authorize('store', Appointment::class);
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
            $this->authorize('show', $appointment);
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
            $this->authorize('update', Appointment::class);
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
            $this->authorize('destroy', Appointment::class);
            self::validatorId($appointmentId, 'appointment_id', 'appointments');
            if (! $this->service->deleteAppointment($appointmentId)) {
                return self::failled('destroy', 'AppointmentController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('destroy', 'AppointmentController', 'delete', $e);
        }
    }

    // for Patient
    public function getAllByPatient(QueryParamRequest $request, int $patientId)
    {
        try {
            $this->authorize('getAllByPatient', [Appointment::class, $patientId]);
            self::validatorId($patientId, 'patient_id', 'patients');
            if (! $appointments = $this->service->getAllByPatient($request, $patientId)) {
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
            $this->authorize('cancelAppointment', [Appointment::class, $patientId]);
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
    public function getAllByDoctor(QueryParamRequest $request, int $doctorId)
    {
        try {
            $this->authorize('getAllByDoctor', [Appointment::class, $doctorId]);
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $appointments = $this->service->getAllByDoctor($request, $doctorId)) {
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
            $this->authorize('updateStatus', [Appointment::class, $doctorId]);
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