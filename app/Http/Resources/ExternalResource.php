<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ExternalAttachmentResource;

class ExternalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_identifier' => $this->external_identifier,
            'full_name' => $this->full_name,
            'organization' => $this->organization,
            'phone' => $this->phone,
            'email' => $this->email,
            'pedagogical_remarks' => $this->pedagogical_remarks,
            'created_at' => $this->created_at,
            'deleted_at' => $this->when($this->trashed(), $this->deleted_at),

            'creator' => new UserResource($this->whenLoaded('creator')),

            'trainings' => $this->whenLoaded('trainings', function () {
                return $this->trainings->map(function ($training) {
                    return [
                        'id' => $training->id,
                        'title' => $training->title,
                        'training_type' => $training->training_type,
                        'start_date' => $training->start_date,
                        'end_date' => $training->end_date,
                        'status' => $training->status,
                        'pivot' => [
                            'training_evaluation' => $training->pivot->training_evaluation,
                            'satisfaction_evaluation' => $training->pivot->satisfaction_evaluation,
                        ]
                    ];
                });
            }),
            'participant_details' => $this->whenLoaded('participant', function () {
                return [
                    'id' => $this->participant->id,
                    'insured' => $this->participant->insured,
                    'attendance_recorded' => $this->participant->attendance_recorded,
                    'comments' => $this->participant->comments,
                ];
            }),
            'attachments' => $this->whenLoaded(
                'attachments',
                fn() => ExternalAttachmentResource::collection($this->attachments)
            ),
            'training_group_id' => $this->training_group_id,
            'training_group' => $this->whenLoaded('trainingGroup', function () {
                return [
                    'id' => $this->trainingGroup->id,
                    'title' => $this->trainingGroup->title,
                    'target_size' => $this->trainingGroup->target_size,
                    'current_size' => $this->trainingGroup->current_size,
                    'remarks' => $this->trainingGroup->remarks,
                ];
            }),
        ];
    }
}
