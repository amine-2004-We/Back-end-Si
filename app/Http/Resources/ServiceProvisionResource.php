<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProvisionResource extends JsonResource
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
            'reference' => $this->reference,
            'provision_date' => $this->provision_date->format('Y-m-d'),
            'amount' => $this->amount,
            'supplier' => $this->supplier,
            'description' => $this->description,
            'type' => $this->type->value,
            'justification_url' => $this->justification_url,

            'budget_line' => $this->whenLoaded('budgetLine', function () {
                return [
                    'id' => $this->budgetLine->id,
                    'code' => $this->budgetLine->code,
                    'label' => $this->budgetLine->label,
                    'remaining_amount' => $this->budgetLine->remaining_amount,
                ];
            }),

            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
