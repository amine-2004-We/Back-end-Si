<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class MedicalRecordResource extends JsonResource
{
    /***
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collaborator_id' => $this->collaborator_id,
            'medical_records_types' => $this->medical_records_types?->value,
            'consultation_date' => $this->consultation_date,
            'filing_date' => $this->filing_date,
            'sent_insurance_date' => $this->sent_insurance_date,
            'document_issuer' => $this->document_issuer,
            'declaration_number' => $this->declaration_number,
            'processing_status' => $this->processing_status?->value,
            'attachment' => $this->attachment,
            'comment' => $this->comment,
            'refusal_reason' => $this->refusal_reason,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
