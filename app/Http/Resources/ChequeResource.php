<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChequeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cheque_id' => $this->cheque_id,
            'number' => $this->number,
            'emission_date' => $this->emission_date->format('Y-m-d'),
            'amount' => $this->amount,
            'status' => $this->status->value,
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'beneficiary' => new BeneficiaryResource($this->whenLoaded('beneficiary')),
            'project_bank_account' => new ProjectBankAccountResource($this->whenLoaded('projectBankAccount')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
        ];
    }
}