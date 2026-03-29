<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationGridOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grid_code' => $this->grid_code,
            'evaluation_code' => $this->evaluation_code,
            'title' => $this->title,
            'task_evaluation_id' => $this->task_evaluation_id,
            'educational_area' => $this->educational_area,
            'targeted_overall_skill' => $this->targeted_overall_skill,
            'sub_skill' => $this->sub_skill,
            'project_id' => $this->project_id,
            'program_id' => $this->program_id,
            'niveau_appreciation' => $this->niveau_appreciation?->value,
            'user_id' => $this->user_id,
            'grid_version' => $this->grid_version,
            'grid_status' => $this->grid_status?->value,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
