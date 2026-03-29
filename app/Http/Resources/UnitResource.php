<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'name' => $this->name,
            'internal_code' => $this->internal_code,
            'partner_code' => $this->partner_code,
            'site_id' => $this->site_id,
            'type' => $this->type->value,
            'number_of_classes' => $this->number_of_classes,
            'status' => $this->status->value,
            'educator_id' => $this->educator_id,
            'observations' => $this->observations,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at,
            'site' => SiteResource::make($this->whenLoaded('site')),
            'educator' => UserResource::make($this->whenLoaded('educator')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'classes' => ClassResource::collection($this->whenLoaded('classes')),

        ];
    }
}
