<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->invoice_id,
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            'invoice_date' => $this->invoice_date->format('Y-m-d H:i:s'),
            'amount' => $this->amount,
            'status' => $this->status ?? 'pending',
            'payment_method' => $this->payment_method
        ];
    }
}
