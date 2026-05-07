<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSlotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slot_id,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'start_time' => $this->start_time->format('Y-m-d H:i:s'),
            'end_time' => $this->end_time->format('Y-m-d H:i:s'),
            'status' => $this->status ?? 'available',
            'max_patients' => $this->max_patients,
            'booked_count' => $this->booked_count ?? 0,
        ];
    }
}
