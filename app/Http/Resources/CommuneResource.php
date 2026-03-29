<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommuneResource extends JsonResource
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
            'name' => $this->name,
            // Include the cercle if it's loaded
            'cercle_id' => $this->cercle_id, // Keep the foreign key
            'cercle' => CercleResource::make($this->whenLoaded('cercle')),
        ];
    }
}