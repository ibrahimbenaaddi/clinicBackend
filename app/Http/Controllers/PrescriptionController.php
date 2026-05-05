<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    use ApiResponse, Helper;

    private PrescriptionService $service;

    public function __construct()
    {
        $this->service = new PrescriptionService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('index', Prescription::class);
            if (! $prescriptions = $this->service->getAllPrescription($request)) {
                return self::failled('index', 'PrescriptionController', 'read');
            }
            return self::readSuccess(PrescriptionResource::collection($prescriptions));
        } catch (Exception $e) {
            return self::failled('index', 'PrescriptionController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrescriptionRequest $request)
    {
        try {
            $this->authorize('store', Prescription::class);
            $credentials = $request->validated();
            if (! $prescription = $this->service->createPrescription($credentials)) {
                return self::failled('store', 'PrescriptionController', 'create');
            }
            return self::createSuccess(new PrescriptionResource($prescription));
        } catch (Exception $e) {
            return self::failled('store', 'PrescriptionController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $prescriptionId)
    {
        try {
            self::validatorId($prescriptionId, 'prescription_id', 'prescriptions');
            if (! $prescription = $this->service->getPrescription($prescriptionId)) {
                return self::failled('show', 'PrescriptionController', 'read');
            };
            return self::readSuccess(new PrescriptionResource($prescription));
        } catch (Exception $e) {
            return self::failled('show', 'PrescriptionController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrescriptionRequest $request, int $prescriptionId)
    {
        try {
            $this->authorize('update', Prescription::class);
            self::validatorId($prescriptionId, 'prescription_id', 'prescriptions');
            $credentials = $request->validated();
            if (! $prescription = $this->service->updatePrescription($credentials, $prescriptionId)) {
                return self::failled('update', 'PrescriptionController', 'update');
            }
            return self::updateSuccess(new PrescriptionResource($prescription));
        } catch (Exception $e) {
            return self::failled('update', 'PrescriptionController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $prescriptionId)
    {
        try {
            $this->authorize('destroy', Prescription::class);
            self::validatorId($prescriptionId, 'prescription_id', 'prescriptions');
            if (! $this->service->deletePrescription($prescriptionId)) {
                return self::failled('destroy', 'PrescriptionController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('destroy', 'PrescriptionController', 'delete', $e);
        }
    }

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $this->authorize('getAllByDoctor', [Prescription::class, $doctorId]);
            self::validatorId($doctorId, 'doctor_id', 'doctors');
            if (! $prescriptions = $this->service->getAllByDoctor($request, $doctorId)) {
                return self::failled('getAllByDoctor', 'PrescriptionController', 'read');
            }
            return self::readSuccess(PrescriptionResource::collection($prescriptions));
        } catch (Exception $e) {
            return self::failled('getAllByDoctor', 'PrescriptionController', 'read', $e);
        }
    }
}
