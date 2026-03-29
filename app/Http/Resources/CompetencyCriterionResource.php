<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetencyCriterionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'scoring_scale' => $this->scoring_scale,
            'display_order' => $this->display_order,
            'weight' => $this->weight,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'competency_grids' => $this->whenLoaded('competencyGrids', function () {
                return $this->competencyGrids->map(function ($grid) {
                    return [
                        'id' => $grid->id,
                        'code' => $grid->code,
                        'title' => $grid->title,
                    ];
                });
            }),
        ];
    }
}
