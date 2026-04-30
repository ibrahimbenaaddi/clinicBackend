<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicalRecordRequest extends FormRequest
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
        $recordId = $this->route('doctorId') ?? null;

        return [
            'appointment_id' => 'sometimes|integer|exists:appointments,appointment_id|unique:medical_records,appointment_id,' . $recordId . ',record_id',
            'diagnosis_code' => 'sometimes|string|max:50',
            'clinical_notes' => 'sometimes|string',
            'symptoms' => 'sometimes|string',
        ];
    }
}
