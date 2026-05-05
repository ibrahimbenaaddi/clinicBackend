<?php

namespace App\Services;

use App\Models\Invoice;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    use ServiceResponse, Searchable;
    private static array $validMethodPayment = [
        'cash',
        'card',
        'insurance',
        'bank_transfer'
    ];
    private static array $validStatus = [
        'pending',
        'paid',
        'cancelled',
        'refunded',
        'overdue'
    ];
    public function getAllInvoices(Request $request)
    {
        try {
            $query = Invoice::query()->with(['appointment.doctor.user', 'appointment.patient.user']);
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            $query = self::whereQuery($query, $request, 'payment_method', self::$validMethodPayment);
            $query = $this->filter($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
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

    public function getAllByPatient(Request $request, int $patientId)
    {
        try {
            $query = Invoice::query()->with(['appointment.doctor.user', 'appointment.patient.user'])
                ->whereHas('appointment.patient', function ($q) use ($patientId) {
                    $q->where('patient_id', $patientId);
                });
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            $query = self::whereQuery($query, $request, 'payment_method', self::$validMethodPayment);
            $query = $this->filter($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByPatient', 'InvoiceService', $e);
        }
    }

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = Invoice::query()->with(['appointment.doctor.user', 'appointment.patient.user'])
                ->whereHas('appointment.doctor', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            $query = self::whereQuery($query, $request, 'payment_method', self::$validMethodPayment);
            $query = $this->filter($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'InvoiceService', $e);
        }
    }

    private function filter(Builder $query, Request $request): Builder
    {
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (int) $request->query('min_amount'));
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (int) $request->query('max_amount'));
        }
        if ($request->filled('search')) {
            $term = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->WhereHas('appointment.doctor.user', function ($uq) use ($term) {
                    $uq->where('firstname', 'like', $term)
                        ->orWhere('lastname',  'like', $term);
                })
                    ->orWhereHas('appointment.patient.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname',  'like', $term);
                    });
            });
        }
        return $query;
    }
}