<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->record_id,
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'diagnosis_code' => $this->diagnosis_code,
            'clinical_notes' => $this->clinical_notes,
            'symptoms' => $this->symptoms,

        ];
    }
}
