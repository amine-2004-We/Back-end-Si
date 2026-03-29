<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'phase_identifier' => $this->phase_identifier,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'execution_order' => $this->execution_order,
            'planned_start_date' => $this->planned_start_date,
            'planned_end_date' => $this->planned_end_date,
            'actual_start_date' => $this->actual_start_date,
            'actual_end_date' => $this->actual_end_date,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'responsible' => new UserResource($this->whenLoaded('responsible')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'deleted_at'=>$this->deleted_at,

        ];
    }
}
