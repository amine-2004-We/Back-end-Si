<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryStatusHistoryResource extends JsonResource
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
            'beneficiary_id' => $this->beneficiary_id,
           'status' => $this->status,
            'reason' => $this->reason,
            'previous_group_id' => $this->previous_group_id,
            'previous_group_name'=>$this->PreviousGroup?->name,
            'transfer_commune_id' => $this->transfer_commune_id,
            'transfer_commune_name'=>$this->TransferCommune?->name,
            'transfer_class_name'=>$this->TransferClass?->class_name,
            'transfer_region_name'=>$this->TransferCommune?->Province?->Region?->name,
            'transfer_province_name'=>$this->TransferCommune?->Province?->name,
            'transfer_class_id' => $this->transfer_class_id,
            'change_date' => $this->change_date,
            'destination_group_id' => $this->destination_group_id,
            'destination_group_name'=>$this->DestinationGroup?->name,
            'created_at' => $this->created_at
        ];
    }
}
