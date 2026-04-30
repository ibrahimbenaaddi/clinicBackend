<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
            'patient_id' => 'sometimes|integer|exists:patients,patient_id',
            'doctor_id' => 'sometimes|integer|exists:doctors,doctor_id',
            'status' => 'sometimes|string|in:pending,confirmed,completed,cancelled,no_show',
            'start_time' => 'sometimes|date|after_or_equal:now|date_format:Y-m-d H:i:s',
            'end_time' => 'sometimes|date|after:start_time|date_format:Y-m-d H:i:s',
            'reason_for_visit' => 'sometimes|string|max:1000|min:10'
        ];
    }
}
