<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
        return [
            'appointment_id' => 'required|integer|exists:appointments,appointment_id|unique:invoices,appointment_id',
            'amount' => 'required|numeric|min:0',
            'invoice_date' => 'required|date|after_or_equal:now|date_format:Y-m-d H:i:s',
            'payment_method' => 'required|string|in:cash,card,insurance,bank_transfer'
        ];
    }
}
