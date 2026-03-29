<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->deleted_at === null ? 'Actif' : 'Inactif';

        return [
            'id' => $this->id,
            'level_id' => $this->level_id,
            'title' => $this->title,
            'code' => $this->code,
            'cycle_id' => $this->cycle_id,
            'order' => $this->order,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'status' => $status,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
            'cycle' => new CycleResource($this->whenLoaded('cycle')),
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
