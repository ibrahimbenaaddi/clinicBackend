<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\services\MedicalRecordService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Validator;

class MedicalRecordController extends Controller
{
    use ApiResponse;

    private MedicalRecordService $service;

    public function __construct()
    {
        $this->service = new MedicalRecordService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (! $records = $this->service->getAllMedicalRecords()) {
                return self::failled('index', 'MedicalRecordController', 'read');
            };
            return self::readSuccess(MedicalRecordResource::collection($records));
        } catch (Exception $e) {
            return self::failled('index', 'MedicalRecordController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalRecordRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $record = $this->service->createMedicalRecord($credentials)) {
                return self::failled('store', 'MedicalRecordController', 'create');
            }
            return self::createSuccess(new MedicalRecordResource($record));
        } catch (Exception $e) {
            return self::failled('store', 'MedicalRecordController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $recordId)
    {
        try {
            $this->validatorId($recordId);
            if (! $record = $this->service->getMedicalRecord($recordId)) {
                return self::failled('show', 'MedicalRecordController', 'read');
            };
            return self::readSuccess(new MedicalRecordResource($record));
        } catch (Exception $e) {
            return self::failled('show', 'MedicalRecordController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalRecordRequest $request, int $recordId)
    {
        try {
            $this->validatorId($recordId);
            $credentials = $request->validated();
            if (! $record = $this->service->updateMedicalRecord($credentials, $recordId)) {
                return self::failled('update', 'MedicalRecordController', 'update');
            }
            return self::updateSuccess(new MedicalRecordResource($record));
        } catch (Exception $e) {
            return self::failled('update', 'MedicalRecordController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $recordId)
    {
        try {
            $this->validatorId($recordId);
            if (! $this->service->deleteMedicalRecord($recordId)) {
                return self::failled('delete', 'MedicalRecordController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'MedicalRecordController', 'delete', $e);
        }
    }

    private function validatorId(int $recordId)
    {
        // protecte your app from XSS by laravel_validation system
        $validator = Validator::make(
            ['recordId' => $recordId],
            [
                'recordId' => 'required|integer|exists:medical_records,record_id',
            ],
            [
                'recordId.exists' => 'record not found',
                'recordId.required' => 'record ID is required',
                'recordId.integer' => 'Invalid record ID format',
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first('recordId'));
        }
    }
}
