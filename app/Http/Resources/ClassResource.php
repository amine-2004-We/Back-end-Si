<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProjectResource;

class ClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id'=>$this->class_id,
            'class_name' => $this->class_name,
            'class_code' => $this->class_code,
            'internal_class_code' => $this->internal_class_code,
            'region_id'=>$this->region_id,
            'external_reference_code' => $this->external_reference_code,
            'unit_id' => $this->unit_id,
            'cycle_id' => $this->cycle_id,
            
            'commune_id'=>$this->douar?->commune_id,
            'commune_name'=>$this->douar?->commune?->name,
            'province_id'=>$this->douar?->commune?->province?->id,
            'province_name'=>$this->douar?->commune?->province?->name,
            'douar_id'=>$this->douar_id,
            'douar_name'=>$this->douar?->name,
            
            'class_type_id' =>$this->class_type_id,
            'class_status_id' => $this->class_status_id,
            'activity_leader' => $this->activity_leader,
            'local_pedagogical_coordinator' => $this->local_pedagogical_coordinator,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'note' => $this->note,
            'class_state' => $this->class_state,
            'class_status_value' => $this->class_status_value,
            'status_change_date' => $this->status_change_date,
            'status_change_reason' => $this->status_change_reason,
            'perpetuation_project_id' => $this->perpetuation_project_id,
            'transfer_to_project_id' => $this->transfer_to_project_id,
            'relocate_to_class_id' => $this->relocate_to_class_id,
            'relocation_date' => $this->relocation_date,
            'user_id'=>$this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

      
            'cycles' => CycleResource::make($this->whenLoaded('cycles')),
            'classType' => ClassTypesResource::make($this->whenLoaded('classType')),
            'classStatus' => ClassStatusResource::make($this->whenLoaded('classStatus')),
            'perpetuationProject' => ProjectResource::make($this->whenLoaded('perpetuationProject')),
            'transferToProject' => ProjectResource::make($this->whenLoaded('transferToProject')),
            'relocateToClass' => ClassResource::make($this->whenLoaded('relocateToClass')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
        ];
    }
}
