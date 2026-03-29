<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'=>$this->id,
            'position_code'=>$this->position_code,
            'department_id'=>$this->department_id,
            'description'=>$this->description,
            'main_mission'=>$this->main_mission,
            'key_activities'=>$this->key_activities,
            'required_skills'=>$this->required_skills,
            'link_with_function'=>$this->link_with_function,
            'version'=>$this->version,
        ];
    }
}

