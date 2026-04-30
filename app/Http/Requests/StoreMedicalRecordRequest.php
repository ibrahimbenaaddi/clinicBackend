<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
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
            'appointment_id' => 'required|integer|exists:appointments,appointment_id|unique:medical_records,appointment_id',
            'diagnosis_code' => 'required|string|max:50',
            'clinical_notes' => 'required|string',
            'symptoms' => 'required|string',
        ];
    }
}
