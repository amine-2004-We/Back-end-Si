<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_identifier' => $this->session_identifier,
           'module' => $this->whenLoaded('module'),
            'training' => $this->whenLoaded('training'),
            'training_group' => $this->whenLoaded('trainingGroup'),
            'animator' => $this->whenLoaded('animator'),
            'session_date' => $this->session_date->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'planned_duration_hours' => $this->planned_duration_hours,
            'site' => new SiteResource($this->whenLoaded('site')),
            'session_type' => $this->session_type,
            'presence_registered' => $this->presence_registered,
            'observations' => $this->observations,
            'status' => $this->status,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'attachments' => TrainingSessionAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at->toIso8601String(),
            'deleted_at'=> $this->deleted_at,
        ];
    }
}
