<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'company_name' => $this->company_name,
            'trade_name' => $this->trade_name,
            'supplier_type' => $this->supplier_type,
            'business_sector' => $this->business_sector,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'rib' => $this->rib,
            'contact_people' => ContactPersonResource::collection($this->whenLoaded('contactPeople')),
            'legal_status' => $this->legal_status,
            'tax_id' => $this->tax_id,
            'commercial_register_number' => $this->commercial_register_number,
            'supporting_documents' => $this->supporting_documents,
            'bank_account_id' => $this->bank_account_id,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ]
            ;
    }
}
