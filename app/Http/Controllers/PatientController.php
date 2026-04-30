<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Services\PatientService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    use ApiResponse;

    private PatientService $service;

    public function __construct()
    {
        $this->service = new PatientService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (! $patients = $this->service->getAllPatients()) {
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
            $this->validatorId($patientId);
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
            $this->validatorId($patientId);
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
            $this->validatorId($patientId);
            if (! $this->service->deletePatient($patientId)) {
                return self::failled('delete', 'PatientController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'PatientController', 'delete', $e);
        }
    }

    private function validatorId(int $patientId)
    {
        // protecte your app from XSS by laravel_validation system
        $validator = Validator::make(
            ['patientId' => $patientId],
            [
                'patientId' => 'required|integer|exists:patients,patient_id',
            ],
            [
                'patientId.exists' => 'Patient not found',
                'patientId.required' => 'Patient ID is required',
                'patientId.integer' => 'Invalid patient ID format',
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first('patientId'));
        }
    }
}
