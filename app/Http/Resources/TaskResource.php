<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'title' => $this->title,
            'project_id' => $this->project_id,
            'program_id' => $this->program_id,
            'phase_id' => $this->phase_id,
            'responsible_collaborator_id' => $this->responsible_collaborator_id,
            'location_site_id' => $this->location_site_id,
            'budget_line_id' => $this->budget_line_id,
            'created_by' => $this->created_by,
            'user_name' => $this->creator?->name,
            'type' => $this->type,
            'expected_start_date' => $this->expected_start_date?->format('Y-m-d'),
            'expected_end_date' => $this->expected_end_date?->format('Y-m-d'),
            'actual_start_date' => $this->actual_start_date?->format('Y-m-d'),
            'actual_end_date' => $this->actual_end_date?->format('Y-m-d'),
            'duration_minutes' => $this->duration_minutes,
            'activities'=>$this->activities,
            'class_id'=>$this->class_id,
            'status' => $this->status,
            'field_observations' => $this->field_observations,
            'associated_document' => $this->associated_document,
            'implementation_method' => $this->implementation_method,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            'details' => $this->getDetailsAttribute(),

            'project' => $this->whenLoaded('project'),
             'financial_installments' => $this->when(
                $this->relationLoaded('project') && $this->project,
                function () {
                    return \App\Http\Resources\FinancialInstallmentResource::collection($this->project->financialInstallments);
                }
            ),
            'program' => new ProgramResource($this->whenLoaded('program')),
            'phase' => $this->whenLoaded('phase'),
            'responsible_collaborator' => new CollaboratorResource($this->whenLoaded('responsibleCollaborator')),
            'location_site' => new SiteResource($this->whenLoaded('locationSite')),
            'budget_line' => $this->whenLoaded('budgetLine'),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'groups' => $this->whenLoaded('groups', function () {
                return $this->groups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'code' => $group->code,
                    ];
                });
            }),
            'attachments' => TaskAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
