<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Ensure relations are present if controller didn't eager load them
        $this->resource->loadMissing(['training', 'createdBy', 'collaborators', 'candidates', 'externals']);

        // Merge all participant relations into one collection
        $participants = collect([])
            ->merge($this->whenLoaded('collaborators'))
            ->merge($this->whenLoaded('candidates'))
            ->merge($this->whenLoaded('externals'));

        return [
            // Core
            'id' => $this->id,
            'group_id' => $this->group_id,
            'title' => $this->title,
            'target_size' => $this->target_size,
            'current_size' => $this->current_size,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'responsible_id' => $this->responsible_id,
            'training_id' => $this->training_id,
            'created_by_id' => $this->created_by_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            // Keep existing per-type counters if you rely on them
            'collaborators_count' => $this->collaborators_count ?? 0,
            'candidates_count' => $this->candidates_count ?? 0,
            'externals_count' => $this->externals_count ?? 0,

            // Unified participants (each item gets display_name + type from ParticipantResource)
            'participants' => ParticipantResource::collection($participants),
            'participants_count' => $participants->count(),

            // Relations
            'training' => $this->whenLoaded('training', fn () => [
                'id' => $this->training->id,
                'training_id' => $this->training->training_id,
                'title' => $this->training->title,
                'training_type' => $this->training->training_type,
                'responsible_id' => $this->training->responsible_id,
                'start_date' => $this->training->start_date,
                'end_date' => $this->training->end_date,
                'status' => $this->training->status,
                'target_audience' => $this->training->target_audience,
                'attachments' => $this->training->attachments ?? [],
                'notes' => $this->training->notes,
                'created_by_id' => $this->training->created_by_id,
                'created_at' => $this->training->created_at,
                'updated_at' => $this->training->updated_at,
                'deleted_at' => $this->training->deleted_at,
            ]),

            'created_by' => $this->whenLoaded('createdBy', fn () => new UserResource($this->createdBy)),

            // Important: DO NOT include raw 'collaborators', 'candidates', 'externals' arrays here,
            // to keep the response front-end friendly and avoid duplication.
        ];
    }
}
