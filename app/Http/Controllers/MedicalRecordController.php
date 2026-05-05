<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\services\MedicalRecordService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    use ApiResponse, Helper;

    private MedicalRecordService $service;

    public function __construct()
    {
        $this->service = new MedicalRecordService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $this->authorize('index', MedicalRecord::class);
            if (! $records = $this->service->getAllMedicalRecords($request)) {
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
            $this->authorize('store', MedicalRecord::class);
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
            self::validatorId($recordId, 'record_id', 'medical_records');
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
            $this->authorize('update', MedicalRecord::class);
            self::validatorId($recordId, 'record_id', 'medical_records');
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
            $this->authorize('destroy', MedicalRecord::class);
            self::validatorId($recordId, 'record_id', 'medical_records');
            if (! $this->service->deleteMedicalRecord($recordId)) {
                return self::failled('delete', 'MedicalRecordController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'MedicalRecordController', 'delete', $e);
        }
    }
}
