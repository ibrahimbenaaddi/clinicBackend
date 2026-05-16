<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentSlotRequest extends FormRequest
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
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'start_time' => 'required|date|after_or_equal:today|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date|after:start_time|date_format:Y-m-d H:i:s',
            'max_patients' => 'required|integer|min:1',
        ];
    }
}
