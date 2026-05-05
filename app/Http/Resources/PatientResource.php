<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->whenLoaded('user')),
            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
            'date_birth' => $this->date_birth->format('Y-m-d'),
            'address' => $this->address,
            'phone' => $this->phone,
            'insurance_info' => $this->insurance_info
        ];
    }
}
