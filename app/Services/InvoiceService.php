<?php

namespace App\Services;

use App\Models\Invoice;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    use ServiceResponse;

    public function getAllInvoices()
    {
        try {
            return Invoice::with(['appointment.doctor.user', 'appointment.patient.user'])->latest()->paginate(10);
        } catch (Exception $e) {
            return self::theLog('getAllInvoices', 'InvoiceService', $e);
        }
    }

    public function createInvoice(array $credentials)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::create($credentials);
            if (blank($invoice)) {
                DB::rollBack();
                return self::theLog('createInvoice', 'InvoiceService');
            }

            $invoice->load(['appointment.doctor.user', 'appointment.patient.user']);
            DB::commit();
            return $invoice;
        } catch (Exception $e) {
            return self::theLog('createInvoice', 'InvoiceService', $e);
        }
    }

    public function getInvoice(int $invoiceId)
    {
        try {
            return Invoice::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($invoiceId);
        } catch (Exception $e) {
            return self::theLog('getInvoice', 'InvoiceService', $e);
        }
    }

    public function updateInvoice(array $credentials, int $invoiceId)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($invoiceId);

            $isUpdated = $invoice->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateInvoice', 'InvoiceService');
            }

            $invoice->refresh();
            $invoice->load(['appointment.doctor.user', 'appointment.patient.user']);

            DB::commit();
            return $invoice;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateInvoice', 'InvoiceService', $e);
        }
    }

    public function deleteInvoice(int $invoiceId)
    {
        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['appointment.doctor.user', 'appointment.patient.user'])->findOrFail($invoiceId);

            $isDeleted = $invoice->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteInvoice', 'InvoiceService');
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteInvoice', 'InvoiceService', $e);
        }
    }
}
