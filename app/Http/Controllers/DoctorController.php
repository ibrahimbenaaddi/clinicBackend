<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Services\DoctorService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    use ApiResponse;

    private DoctorService $service;

    public function __construct()
    {
        $this->service = new DoctorService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (! $doctors = $this->service->getAllDoctors()) {
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
            $this->validatorId($doctorId);
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
            $this->validatorId($doctorId);
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
            $this->validatorId($doctorId);
            if (! $this->service->deleteDoctor($doctorId)) {
                return self::failled('delete', 'DoctorController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'DoctorController', 'delete', $e);
        }
    }

    private function validatorId(int $doctorId)
    {
        // protecte your app from XSS by laravel_validation system
        $validator = Validator::make(
            ['doctorId' => $doctorId],
            [
                'doctorId' => 'required|integer|exists:doctors,doctor_id',
            ],
            [
                'doctorId.exists' => 'Doctor not found',
                'doctorId.required' => 'Doctor ID is required',
                'doctorId.integer' => 'Invalid doctor ID format',
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first('doctorId'));
        }
    }
}
