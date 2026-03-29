<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallForProjectResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'debut_date' => $this->debut_date,
            'end_date' => $this->end_date,
            'estimated_budget' => $this->estimated_budget,  
            'responsible_id'=> $this->responsible_id,
            'responsible_name'=> $this->responsible ? $this->responsible->first_name.' '.$this->responsible->last_name : null,
            'status' => $this->status,
            'registration_link' => $this->registration_link,
            'type' => $this->type,
            'selection_criteria' => $this->selection_criteria,
            'required_documents' => $this->required_documents,
            'offer_type' => $this->offer_type,
            'sponsor_id' => $this->sponsor_id,
            'sponsor_name' => $this->sponsor ? $this->sponsor->partner_name : null,
            'submission_date' => $this->submission_date,
            'submission_indicators' => $this->submission_indicators,
            'expertise_areas' => $this->expertise_areas,
            'target_audience' => $this->target_audience,
            'project_duration' => $this->project_duration,
            'potential_profiles' => $this->potential_profiles,
            'required_resources' => $this->required_resources,
            'work_plan' => $this->work_plan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->created_by,
            'creator_name' => $this->creator ? $this->creator->name:null

        ];
    }
}
