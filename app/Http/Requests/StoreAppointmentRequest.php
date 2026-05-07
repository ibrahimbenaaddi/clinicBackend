<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'slot_id' => [
                'required',
                'integer',
                'exists:appointment_slots,slot_id',
                Rule::unique('appointments', 'slot_id')
                    ->where(function ($q) {
                        $q->where('patient_id', $this->patient_id)
                            ->where('status', '!=', 'cancelled');
                    }),
            ],
            'reason_for_visit' => 'required|string|max:1000|min:10',
        ];
    }
}
