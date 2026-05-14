<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->doctor_id,
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'specialization' => $this->specialization,
            'license_number' => $this->license_number,
            'phone' => $this->phone,
        ];
    }
}