<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PresenceResource extends JsonResource
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
            'presence_id' => $this->presence_id,
            'person' => [
                'id' => $this->personable->id,
                'type' => class_basename($this->personable_type),
                'name' => $this->personable->first_name . ' ' . $this->personable->last_name,
            ],
            'task' => new TaskResource($this->whenLoaded('task')),
            'event_date' => $this->event_date,
            'status' => $this->status,
            'arrival_time' => $this->arrival_time,
            'justification' => $this->justification,
            'observations' => $this->observations,
            'declarer' => new UserResource($this->whenLoaded('declarer')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
