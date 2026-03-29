<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypeDepartmentResource extends JsonResource
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
            'superior_type_id' => $this->superior_type_id,
            'superior_type_name' => $this->superiorType?->name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
