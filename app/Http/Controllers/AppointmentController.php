<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class AppointmentController extends Controller
{
    use ApiResponse;

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
            $this->validatorId($appointmentId);
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
            $this->validatorId($appointmentId);
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
            $this->validatorId($appointmentId);
            if (! $this->service->deleteAppointment($appointmentId)) {
                return self::failled('delete', 'AppointmentController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'AppointmentController', 'delete', $e);
        }
    }

    private function validatorId(int $appointmentId)
    {
        // protecte your app from XSS by laravel_validation system
        $validator = Validator::make(
            ['appointmentId' => $appointmentId],
            [
                'appointmentId' => 'required|integer|exists:appointments,appointment_id',
            ],
            [
                'appointmentId.exists' => 'Appointment not found',
                'appointmentId.required' => 'Appointment ID is required',
                'appointmentId.integer' => 'Invalid Appointment ID format',
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first('appointmentId'));
        }
    }
}
