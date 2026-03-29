<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'grant_id' => $this->grant_id,
            'partner_id' => $this->partner_id,
            'partner_name'=>$this->partner?->partner_name,
            'convention_id' => $this->convention_id,
            'convention_name'=>$this->convention?->title,
            'project_id' => $this->project_id,
            'project_name'=>$this->project?->project_name,
            'convention_currency'=>$this->convention?->devise,
            'bank_account_id' => $this->bank_account_id,
            'bank_account_name'=>$this->bankAccount?->account_title,
            'committed_amount' => $this->committed_amount,
            'received_amount' => $this->received_amount,
            'currency' => $this->currency,
            'agreement_date' => $this->agreement_date,
            'received_dates' => $this->received_dates,
            'reception_method' => $this->reception_method,
            'intended_use' => $this->intended_use,
            'status' => $this->status,
            'comments' => $this->comments,
            'proof_document_attachment_path' => $this->proof_document_attachment_path,
            'payment_schedule_attachment_path' => $this->payment_schedule_attachment_path,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by'=>$this->user?->name,

        ];
    }
}
