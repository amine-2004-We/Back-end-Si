<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationCriteriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            "id"=>$this->id,
            "criteria_code"=>$this->criteria_code,
            "name"=>$this->name,
            "evaluation_grid_id"=>$this->evaluation_grid_id,
            "object_project"=>$this->object_project,
            "object_partner"=>$this->object_partner,
            "description"=>$this->description,
            "grading_scale"=>$this->grading_scale,
            "weighting_criterion"=>$this->weighting_criterion,
            "order"=>$this->order,
            "comments"=>$this->comments,
            "created_at"=>$this->created_at,
            "deleted_at"=>$this->deleted_at,
            'creator' => UserResource::make($this->whenLoaded('creator')),
            "created_by" => $this->created_by,
        ];
    }
}
