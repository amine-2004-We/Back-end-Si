<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationHrResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id"=>$this->id,
            "name"=>$this->name,
            "description"=>$this->description,
            "objectif_nature"=>$this->objectif_nature,
            "measurement_indicators"=>$this->measurement_indicators,
            "weight"=>$this->weight,
            "skill"=>$this->skill,
            "indicators"=>$this->indicators,
            "value"=>$this->value
        ];
    }

}
