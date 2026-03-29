<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->project_name,
            'position_id' => $this->position_id,
            'position_name' => $this->position?->title,
            'launch_date' => $this->launch_date,
            'closing_date' => $this->closing_date,
            'type' => $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
