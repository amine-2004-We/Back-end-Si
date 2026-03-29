<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'advance_code'    => $this->advance_code,
            'project_id'      => $this->project_id,
            'collaborator_id' => $this->collaborator_id,
            'advance_type'    => $this->advance_type,
            'advance_amount'  => $this->advance_amount,
            'proof_expected'  => $this->proof_expected,
            'proof_status'    => $this->proof_status,
            'created_by'      => $this->created_by,
        ];
    }
}
