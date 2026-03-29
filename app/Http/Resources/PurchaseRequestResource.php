<?php

namespace App\Http\Resources;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->purchase_request_header_id,
            'departement' => $this->departement?->name,
            'project' => $this->project?->name,
            'requester' => $this->requester?->name,
            'status' => $this->status,
            'status_label' => PurchaseRequestStatus::from($this->status)->label(),
            'observation' => $this->observation,
            'priority'=>$this->priority,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
