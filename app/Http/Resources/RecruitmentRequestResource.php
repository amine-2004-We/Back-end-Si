<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruitmentRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_id' => $this->request_id,
            'department_id' => $this->department_id,
            'department_name' => $this->department?->name,
            'position_id' => $this->position_id,
            'position_name' => $this->position?->title,
            'number_of_positions' => $this->number_of_positions,
            'recruitment_reason' => $this->recruitment_reason,
            'required_skills' => $this->required_skills,
            'desired_start_date' => $this->desired_start_date,
            'status' => $this->status,
            'replaced_collaborator_id' => $this->replaced_collaborator_id,
            'collaborator_name' => $this->replacedCollaborator?->first_name . ' ' . $this->replacedCollaborator?->last_name,
            'replacement_reason' => $this->replacement_reason,
            'exit_date' => $this->exit_date,
            'province_id' => $this->province_id,
            'province_name' => $this->province?->name,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->project_name,
            'job_file' => $this->job_file,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'creator'=>RecruitmentRequestCreatorResource::make($this->whenLoaded('creator')),
            'created_by'=>$this->created_by
        ];
    }
}
