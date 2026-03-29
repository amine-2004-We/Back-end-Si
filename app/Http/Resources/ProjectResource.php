<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * class ProjectResource
 */
class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        // Include financial installments when relation is loaded
        $data['financial_installments'] = \App\Http\Resources\FinancialInstallmentResource::collection($this->whenLoaded('financialInstallments'));

        return $data;
    }
}
