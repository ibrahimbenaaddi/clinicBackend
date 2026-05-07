<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class availableSlotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalSpots = $this->max_patients ?? 1;
        $bookedCount = $this->booked_count ?? 0;

        return [
            'id' => $this->slot_id,
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'start_time_utc' => $this->start_time->toISOString(),
            'end_time_utc' => $this->end_time->toISOString(),
            'duration_minutes' => $this->start_time->diffInMinutes($this->end_time),
            'is_available' => $bookedCount < $totalSpots && $this->status === 'available',
            'available_spots' => max(0, $totalSpots - $bookedCount),
            'booked_count' => $bookedCount,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
        ];
    }
}
