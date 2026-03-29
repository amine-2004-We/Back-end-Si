<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalltenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'calltender_id' => $this->calltender_id,
            'responsible_name' => $this->responsible?->name,
            'supplier' => $this->supplier,
            'subject' => $this->subject,
            'purchase_order_refs' => $this->purchase_order_refs,
            'calltender_type' => $this->calltender_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_amount' => $this->total_amount,
            'currency' => $this->currency,
            'conditions_path' => $this->conditions_path,
            'responsible_id' => $this->responsible_id,
            'status' => $this->status,
            'signature_date' => $this->signature_date,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
