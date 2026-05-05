<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Services\DoctorService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    use ApiResponse, Helper;

    private DoctorService $service;

    public function __construct()
    {
        $this->service = new DoctorService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if (! $doctors = $this->service->getAllDoctors($request)) {
                return self::failled('index', 'DoctorController', 'read');
            };
            return self::readSuccess(DoctorResource::collection($doctors));
        } catch (Exception $e) {
            return self::failled('index', 'DoctorController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorRequest $request)
    {
        try {
            $this->authorize('store', Doctor::class);
            $credentials = $request->validated();
            if (! $doctor = $this->service->createDoctor($credentials)) {
                return self::failled('store', 'DoctorController', 'create');
            }
            return self::createSuccess(new DoctorResource($doctor));
        } catch (Exception $e) {
            return self::failled('store', 'DoctorController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $doctorId)
    {
        try {
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $doctor = $this->service->getDoctor($doctorId)) {
                return self::failled('show', 'DoctorController', 'read');
            };
            return self::readSuccess(new DoctorResource($doctor));
        } catch (Exception $e) {
            return self::failled('show', 'DoctorController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request, int $doctorId)
    {
        try {
            $this->authorize('update', [Doctor::class, $doctorId]);
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            $credentials = $request->validated();
            if (! $doctor = $this->service->updateDoctor($credentials, $doctorId)) {
                return self::failled('update', 'DoctorController', 'update');
            }
            return self::updateSuccess(new DoctorResource($doctor));
        } catch (Exception $e) {
            return self::failled('update', 'DoctorController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $doctorId)
    {
        try {
            $this->authorize('destroy', Doctor::class);
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $this->service->deleteDoctor($doctorId)) {
                return self::failled('destroy', 'DoctorController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('destroy', 'DoctorController', 'delete', $e);
        }
    }
}