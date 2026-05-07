<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentSlotRequest extends FormRequest
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
            'doctor_id' => 'sometimes|integer|exists:doctors,doctor_id',
            'start_time' => 'sometimes|date|after_or_equal:today|date_format:Y-m-d H:i:s',
            'end_time' => 'sometimes|date|after:start_time|date_format:Y-m-d H:i:s',
            'status' => 'sometimes|string|in:available,blocked,full,cancelled',
            'max_patients' => 'required|integer|min:10',
        ];
    }
}
