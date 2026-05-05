<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    use ApiResponse, Helper;

    private PatientService $service;

    public function __construct()
    {
        $this->service = new PatientService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('index', Patient::class);
            if (! $patients = $this->service->getAllPatients($request)) {
                return self::failled('index', 'PatientController', 'read');
            };
            return self::readSuccess(PatientResource::collection($patients));
        } catch (Exception $e) {
            return self::failled('index', 'PatientController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        try {
            $this->authorize('store', Patient::class);
            $credentials = $request->validated();
            if (! $patient = $this->service->createPatient($credentials)) {
                return self::failled('store', 'PatientController', 'create');
            }
            return self::createSuccess(new PatientResource($patient));
        } catch (Exception $e) {
            return self::failled('store', 'PatientController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $patientId)
    {
        try {
            self::validatorId($patientId, 'patient_id', 'patients');
            if (! $patient = $this->service->getPatient($patientId)) {
                return self::failled('show', 'PatientController', 'read');
            };
            return self::readSuccess(new PatientResource($patient));
        } catch (Exception $e) {
            return self::failled('show', 'PatientController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, int $patientId)
    {
        try {
            $this->authorize('update', [Patient::class, $patientId]);
            self::validatorId($patientId, 'patient_id', 'patients');
            $credentials = $request->validated();
            if (! $patient = $this->service->updatePatient($credentials, $patientId)) {
                return self::failled('update', 'PatientController', 'update');
            }
            return self::updateSuccess(new PatientResource($patient));
        } catch (Exception $e) {
            return self::failled('update', 'PatientController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $patientId)
    {
        try {
            $this->authorize('destroy', Patient::class);
            self::validatorId($patientId, 'patient_id', 'patients');
            if (! $this->service->deletePatient($patientId)) {
                return self::failled('delete', 'PatientController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'PatientController', 'delete', $e);
        }
    }
}
