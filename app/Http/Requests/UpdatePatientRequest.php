<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
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
            'firstname' => 'sometimes|string|max:60|min:3',
            'lastname' => 'sometimes|string|max:60|min:3',
            'date_birth' => 'sometimes|date|before:today|after:1900-01-01',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
            'insurance_info' => 'sometimes|string|max:255'
        ];
    }
}
