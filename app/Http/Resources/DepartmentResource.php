<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'type_departement_id' => $this->type_departement_id,
            'type_departement_name' => $this->typeDepartement?->name,
            'parent_departement_id' => $this->parent_departement_id,
            'departement_name' => $this->departement?->name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
