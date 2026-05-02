<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use App\Traits\Helper;
use Exception;

class InvoiceController extends Controller
{

    use ApiResponse, Helper;

    private InvoiceService $service;

    public function __construct()
    {
        $this->service = new InvoiceService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (! $invoices = $this->service->getAllInvoices()) {
                return self::failled('index', 'InvoiceController', 'read');
            }
            return self::readSuccess(InvoiceResource::collection($invoices));
        } catch (Exception $e) {
            return self::failled('index', 'InvoiceController', 'read', $e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $invoice = $this->service->createInvoice($credentials)) {
                return self::failled('store', 'InvoiceController', 'create');
            }
            return self::createSuccess(new InvoiceResource($invoice));
        } catch (Exception $e) {
            return self::failled('store', 'InvoiceController', 'create', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $invoiceId)
    {
        try {
            self::validatorId($invoiceId, 'invoice_id', 'invoices');
            if (! $invoice = $this->service->getInvoice($invoiceId)) {
                return self::failled('show', 'InvoiceController', 'read');
            };
            return self::readSuccess(new InvoiceResource($invoice));
        } catch (Exception $e) {
            return self::failled('show', 'InvoiceController', 'read', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, int $invoiceId)
    {
        try {
            self::validatorId($invoiceId, 'invoice_id', 'invoices');
            $credentials = $request->validated();
            if (! $invoice = $this->service->updateInvoice($credentials, $invoiceId)) {
                return self::failled('update', 'InvoiceController', 'update');
            }
            return self::updateSuccess(new InvoiceResource($invoice));
        } catch (Exception $e) {
            return self::failled('update', 'InvoiceController', 'update', $e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $invoiceId)
    {
        try {
            self::validatorId($invoiceId, 'invoice_id', 'invoices');
            if (! $this->service->deleteInvoice($invoiceId)) {
                return self::failled('delete', 'InvoiceController', 'delete');
            }
            return self::deleteSuccess();
        } catch (Exception $e) {
            return self::failled('delete', 'InvoiceController', 'delete', $e);
        }
    }
}
