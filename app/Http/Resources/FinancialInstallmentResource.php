<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialInstallmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'convention_id' => $this->convention_id,
            'convention' => $this->whenLoaded('convention'),
            'installment_number' => $this->installment_number,
            'amount' => $this->amount,
            'amount_received' => $this->amount_received,
            'reception_mode' => $this->reception_mode,
            'reception_date' => optional($this->reception_date)->toDateTimeString(),
            'is_ttc' => (bool) $this->is_ttc,
            'due_date' => optional($this->due_date)->toDateString(),
            'trigger_condition' => $this->trigger_condition,
            'status' => $this->status,
            'proof_document' => $this->proof_document,
            'devise' => $this->devise,
            'receptions' => \App\Http\Resources\FinancialReceptionResource::collection($this->whenLoaded('receptions')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
