<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Import the new/updated geographic resources
use App\Http\Resources\CommuneResource;
use App\Http\Resources\DouarResource;
use App\Http\Resources\UserResource; // Assuming you have this for creator/manager

class SiteResource extends JsonResource
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
            'site_id' => $this->site_id,
            'name' => $this->name,
            'internal_code' => $this->internal_code,
            'type' => $this->type,
            'country' => $this->country,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'status' => $this->status,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'observations' => $this->observations,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at ,
            'commune_id' => $this->commune_id,
            'commune' => CommuneResource::make($this->whenLoaded('commune')),

            'douar_id' => $this->douar_id,
            'douar' => DouarResource::make($this->whenLoaded('douar')),


            'created_by' => $this->created_by,
            'creator' => UserResource::make($this->whenLoaded('creator')),
        ];
    }
}
