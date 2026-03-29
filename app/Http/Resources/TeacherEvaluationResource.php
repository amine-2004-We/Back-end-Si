<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherEvaluationResource extends JsonResource
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
            'teacher_id' => $this->teacher_id,
            'teacher_name'=>$this->teacher?->first_name . ' ' . $this->teacher?->last_name,
            'superior_id'=>$this->creator?->collaborator?->superior?->user?->id,
            'program_id' => $this->program_id,
            'program_title'=>$this->program?->title,
            'program_type_id' => $this->program_type_id,
            'program_type_name'=>$this->programType?->name,
            'unit_id' => $this->unit_id,
            'unit_name'=>$this->unit?->name,
            'general_appearance' => $this->general_appearance,
            'cleanliness' => $this->cleanliness,
            'punctuality' => $this->punctuality,
            'respect_session_schedule' => $this->respect_session_schedule,
            'preparation' => $this->preparation,
            'relationship_with_beneficiaries' => $this->relationship_with_beneficiaries,
            'treatment_of_objectives' => $this->treatment_of_objectives,
            'assessment' => $this->assessment,
            'innovation' => $this->innovation,
            'pedagogical_approach' => $this->pedagogical_approach,
            'participation' => $this->participation,
            'error_correction' => $this->error_correction,
            'comprehension' => $this->comprehension,
            'knowledge_ritualization' => $this->knowledge_ritualization,
            'total_score' => $this->total_score,
            'percentage' => $this->percentage,
            'low_score_reason' => $this->low_score_reason,
            'status' => $this->status,
            'observations' => $this->observations,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by'=>$this->creator?->name,
            'created_by_id'=>$this->created_by,
        ];

    }
}
