<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationGridResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "grid_code"=>$this->grid_code,
            "title"=>$this->title,
            "object_project"=>$this->object_project,
            "object_partner"=>$this->object_partner,
            "evaluation_frequency"=>$this->evaluation_frequency,
            "scoring_method"=>$this->scoring_method,
            "rating_scale"=>$this->rating_scale,
            "weighting_criterion"=>$this->weighting_criterion,
            "grid_status"=>$this->grid_status,
            "comment"=>$this->comment,
            "attachment"=>$this->attachment,
            "created_by"=>$this->created_by,
            "created_at"=>$this->created_at,
            "deleted_at"=>$this->deleted_at
        ];
    }
}
