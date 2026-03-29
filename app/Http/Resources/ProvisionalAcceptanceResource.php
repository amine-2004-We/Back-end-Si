<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvisionalAcceptanceResource extends JsonResource
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
            'identifier' => $this->identifier,
            'delivery_receipt_id' => $this->delivery_receipt_id,
            'provisional_acceptance_date' => $this->provisional_acceptance_date->format('Y-m-d'),
            'reserves' => $this->reserves,
            'corrective_actions' => $this->corrective_actions,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            'deliveryReceipt' => $this->whenLoaded('deliveryReceipt'),
            'committeeMembers' => $this->whenLoaded('committeeMembers'),
            'items' => $this->whenLoaded('items'),
            'partialReceipts' => $this->whenLoaded('partialReceipts'),
        ];
    }
}
