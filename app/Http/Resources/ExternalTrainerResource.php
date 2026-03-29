<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExternalTrainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainer_identifier' => $this->trainer_identifier,
            'full_name' => $this->full_name,
            'affiliation_type' => $this->affiliation_type,
            'phone' => $this->phone,
            'email' => $this->email,
            'cv_url' => $this->cv_path ? Storage::url($this->cv_path) : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'deleted_at' => $this->when($this->trashed(), $this->deleted_at),

            // From base Trainer model
            'trainer_id' => $this->trainer->id,
            'is_available' => $this->trainer->is_available,
            'interventions_evaluation' => $this->trainer->interventions_evaluation,
            'remarks' => $this->trainer->remarks,
            'cabinet_id'=>$this->cabinet_id,


            // Relationships
            'cabinet' => $this->whenLoaded('cabinet', function () {
                return [
                    'id' => $this->cabinet->id,
                    'name' => $this->cabinet->name,
                ];
            }),
            'creator' => new UserResource($this->whenLoaded('creator')),

        ];
    }
}
