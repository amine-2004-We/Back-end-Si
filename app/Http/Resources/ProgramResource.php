<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'title' => $this->title,
            'code' => $this->code,
            'project_id' => $this->project_id,
            'main_objective' => $this->main_objective,
            'type' => $this->type,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'planned_activities_count' => $this->planned_activities_count,
            'status' => $this->status,
            'operational_manager_id' => $this->operational_manager_id,
            'pedagogical_manager_id' => $this->pedagogical_manager_id,
            'regional_manager_id' => $this->regional_manager_id,
            'supervisor_id' => $this->supervisor_id,
            'observations' => $this->observations,
            'created_by' => $this->created_by,
           
             'intervention_axis_id'=>$this->intervention_axis_id,
             'intervention_axis_name'=>$this->interventionAxis?->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            'project' => ProjectResource::make($this->whenLoaded('project')),
            'operational_manager' => CollaboratorResource::make($this->whenLoaded('operationalManager')),
            'pedagogical_manager' => CollaboratorResource::make($this->whenLoaded('pedagogicalManager')),
            'regional_manager' => CollaboratorResource::make($this->whenLoaded('regionalManager')),
            'supervisor' => CollaboratorResource::make($this->whenLoaded('supervisor')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
          'program_types'=>ProgramTypeResource::collection($this->whenLoaded('programTypes')),
          'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
       

        ];
    }
}