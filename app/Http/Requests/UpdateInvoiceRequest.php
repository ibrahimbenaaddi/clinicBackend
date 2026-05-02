<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoiceId = $this->route('invoiceId') ?? null;

        return [
            'appointment_id' => 'sometimes|integer|exists:appointments,appointment_id|unique:invoices,appointment_id,' . $invoiceId . ',invoice_id',
            'amount' => 'sometimes|numeric|min:0',
            'invoice_date' => 'sometimes|date|after_or_equal:now|date_format:Y-m-d H:i:s',
            'status' => 'sometimes|in:pending,paid,cancelled,refunded,overdue',
            'payment_method' => 'sometimes|string|in:cash,card,insurance,bank_transfer'
        ];
    }
}
