<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationCriteriaOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'criteria_id' => $this->criteria_id,
            'grid_evaluation_id' => $this->grid_evaluation_id,
            'title' => $this->title,
            'criteria_code' => $this->criteria_code,
            'grade_level' => $this->grade_level,
            'evaluation_type' => $this->evaluation_type,
            'weighting' => $this->weighting,
            'niveau_appreciation' => $this->niveau_appreciation,
            'success_indicators' => $this->success_indicators,
            'comments' => $this->comments,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
