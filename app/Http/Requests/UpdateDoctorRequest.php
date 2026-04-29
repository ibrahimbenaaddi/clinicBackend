<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
    public function rules()
    {
        $doctor = $this->route('doctorId');
        $doctorId = $this->route('doctorId') ?? null;


        return [
            'firstname' => 'sometimes|string|max:60|min:3',
            'lastname' => 'sometimes|string|max:60|min:3',
            // 'email' => 'sometimes|email|unique:users,email,' . $doctorId . ',user_id',
            // 'password' => 'sometimes|string|min:8|confirmed',
            'specialization' => 'sometimes|string|in:cardiology,dermatology,neurology,pediatrics,orthopedics,ophthalmology',
            'license_number' => 'sometimes|string|unique:doctors,license_number,' . $doctorId . ',doctor_id',
            'phone' => 'sometimes|string',
        ];
    }
}
