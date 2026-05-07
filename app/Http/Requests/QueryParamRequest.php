<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QueryParamRequest extends FormRequest
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
            'search'          => 'sometimes|string|max:255',
            'status'          => 'sometimes|string|max:50',
            'date'            => 'sometimes|date',
            'from'            => 'sometimes|date',
            'to'              => 'sometimes|date|after_or_equal:from',
            'specialization'  => 'sometimes|string|max:100',
            'payment_method'  => 'sometimes|string|max:50',
            'max_amount'      => 'sometimes|numeric|min:0',
            'min_amount'      => 'sometimes|numeric|min:0',
            'diagnosis_code'  => 'sometimes|string|max:20',
            'page'            => 'sometimes|integer|min:1',
        ];
    }
}
