<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'evaluation_code' => $this->evaluation_code,
            'beneficiary_id' => $this->beneficiary_id,
            'session_id' => $this->session_id,
            'evaluator' => $this->evaluator,
            'comment' => $this->comment,
            'evaluation_status_operation' => $this->evaluation_status_operation->value,
            'attachment' => $this->attachment,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
