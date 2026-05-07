<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueryParamRequest;
use App\Http\Requests\StoreAppointmentSlotRequest;
use App\Http\Requests\UpdateAppointmentSlotRequest;
use App\Http\Resources\AppointmentSlotResource;
use App\Models\AppointmentSlot;
use App\Services\AppointmentSlotService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSlotController extends Controller
{
    use ApiResponse, Helper;

    private AppointmentSlotService $service;

    public function __construct()
    {
        $this->service = new AppointmentSlotService();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(QueryParamRequest $request)
    {
        try {
            $this->authorize('index', AppointmentSlot::class);
            if (! $slots = $this->service->getAllSlots($request)) {
                return self::failled('index', 'AppointmentSlotController', 'read');
            };
            return self::readSuccess(AppointmentSlotResource::collection($slots));
        } catch (Exception $e) {
            return self::failled('index', 'AppointmentSlotController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentSlotRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $slot = $this->service->createSlot($credentials)) {
                return self::failled('store', 'AppointmentSlotController', 'create');
            }
            return self::createSuccess(new AppointmentSlotResource($slot));
        } catch (Exception $e) {
            return self::failled('store', 'AppointmentSlotController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $slotId)
    {
        try {
            self::validatorId($slotId, 'slot_id', 'appointment_slots');
            if (! $slot = $this->service->getSlot($slotId)) {
                return self::failled('show', 'AppointmentSlotController', 'read');
            };
            $this->authorize('show', $slot);
            return self::readSuccess(new AppointmentSlotResource($slot));
        } catch (Exception $e) {
            return self::failled('show', 'AppointmentSlotController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentSlotRequest $request, int $slotId)
    {
        try {
            self::validatorId($slotId, 'slot_id', 'appointment_slots');
            $credentials = $request->validated();
            if (! $slot = $this->service->updateSlot($credentials, $slotId)) {
                return self::failled('update', 'AppointmentSlotController', 'update');
            }
            return self::updateSuccess(new AppointmentSlotResource($slot));
        } catch (Exception $e) {
            return self::failled('update', 'AppointmentSlotController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $slotId)
    {
        try {
            self::validatorId($slotId, 'slot_id', 'appointment_slots');
            if (! $this->service->deleteSlot($slotId)) {
                return self::failled('destroy', 'AppointmentSlotController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('destroy', 'AppointmentSlotController', 'delete', $e);
        }
    }

    public function availableByDoctor(QueryParamRequest $request, int $doctorId)
    {
        try {
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $slots = $this->service->availableByDoctor($request, $doctorId)) {
                return self::failled('availableByDoctor', 'AppointmentSlotController', 'read');
            };
            return self::readSuccess(new JsonResource($slots));
        } catch (Exception $e) {
            return self::failled('availableByDoctor', 'AppointmentSlotController', 'read', $e);
        }
    }

    public function getAllByDoctor(QueryParamRequest $request, int $doctorId)
    {
        try {
            $this->authorize('getAllByDoctor', [AppointmentSlot::class, $doctorId]);
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $slots = $this->service->getAllByDoctor($request, $doctorId)) {
                return self::failled('getAllByDoctor', 'AppointmentSlotController', 'read');
            };
            return self::readSuccess(AppointmentSlotResource::collection($slots));
        } catch (Exception $e) {
            return self::failled('getAllByDoctor', 'AppointmentSlotController', 'read', $e);
        }
    }
}
