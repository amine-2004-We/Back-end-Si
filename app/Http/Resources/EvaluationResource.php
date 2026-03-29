<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'evaluation_code' => $this->evaluation_code,
            'object_project' => $this->object_project,
            'object_partner' => $this->object_partner,
            'evaluation_type' => $this->evaluation_type->value,
            'evaluation_date' => $this->evaluation_date,
            'evaluation_period_start_date' => $this->evaluation_period_start_date,
            'evaluation_period_end_date' => $this->evaluation_period_end_date,
            'evaluator_id' => $this->evaluator_id,
            'total_score' => $this->total_score,
            'comment' => $this->comment,
            'evaluation_status' => $this->evaluation_status->value,
            'notification' => $this->notification,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
