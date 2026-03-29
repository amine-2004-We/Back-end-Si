<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceResource extends JsonResource
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
            'insurance_id' => $this->insurance_id,
            'collaborator_id' => $this->collaborator_id,
            'insurance_type' => $this->insurance_type,
            'collaborator_name' => $this->collaborator?->first_name . ' ' . $this->collaborator?->last_name,
            'insurance_organization' => $this->insurance_organization,
            'affiliation_date' => $this->affiliation_date,
            'termination_date' => $this->termination_date,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
