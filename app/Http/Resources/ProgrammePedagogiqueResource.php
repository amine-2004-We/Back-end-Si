<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ClassResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProjectResource;

class ProgrammePedagogiqueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'date_prevu' => $this->date_prevu,
            'date_realisation' => $this->date_realisation,
            'subjects' => $this->subjects,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'observation' => $this->observation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'class' => ClassResource::make($this->whenLoaded('class')),
            'educator' => UserResource::make($this->whenLoaded('user')),
            'project' => ProjectResource::make($this->whenLoaded('project')),
        ];
    }
}
