<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'beneficiary_id' => $this->beneficiary_id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'place_of_residence' => $this->place_of_residence,
            'massar_code' => $this->massar_code,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'current_school_level_id' => $this->current_school_level_id,
            'group_id' => $this->group_id,
            'class_id'=> $this->group?->class?->id,
            'class_name'=> $this->group?->class?->class_name,
            'status' => $this->status,
            'enrollment_date' => $this->enrollment_date?->format('Y-m-d'),
            'radiation_date' => $this->radiation_date?->format('Y-m-d'),
            'radiation_reason' => $this->radiation_reason,
            'observations' => $this->observations,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            'parents' => $this->whenLoaded('parents', function () {
                return $this->parents->map(function ($parent) {
                    return [
                        'id' => $parent->id,
                        'first_name' => $parent->first_name,
                        'last_name' => $parent->last_name,
                        'name' => $parent->first_name . ' ' . $parent->last_name,
                        'primary_phone' => $parent->primary_phone,
                        'cin' => $parent->cin,
                        'address' => $parent->address,
                        'pivot' => [
                            'legal_role' => $parent->pivot->legal_role,
                        ],
                    ];
                });
            }),
            
            'group' => $this->whenLoaded('group', function () {
                return [
                    'id' => $this->group->id,
                    'name' => $this->group->name,
                    'code' => $this->group->code,
                    'classe' => $this->when($this->group->relationLoaded('class') && $this->group->class, function () {
                        return [
                            'id' => $this->group->class->id,
                            'name' => $this->group->class->class_name, 
                        ];
                    }),
                ];
            }),
            'current_school_level' => LevelResource::make($this->whenLoaded('currentSchoolLevel')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'superior_id'=>$this->creator?->collaborator?->superior?->user?->id,
            'class' => ClassResource::make($this->whenLoaded('class')),
            'cycle' => CycleResource::make($this->whenLoaded('cycle')),
            'consultation_status' => $this->consultation_status,
            'has_orl_test' => (bool) $this->has_orl_test,
            'has_pediatre_test' => (bool) $this->has_pediatre_test,
            'has_vision_test' => (bool) $this->has_vision_test,
            'has_dentaire_test' => (bool) $this->has_dentaire_test,
            'country_id' => $this->country_id,
            'country' => $this->country?->name,
            'fr_grade_s1' => $this->fr_grade_s1,
            'fr_grade_s2_minus_1' => $this->fr_grade_s2_minus_1,
            'fr_grade_s2' => $this->fr_grade_s2,
            'maths_grade_s2_minus_1' => $this->maths_grade_s2_minus_1,
            'maths_grade_s1' => $this->maths_grade_s1,
            'maths_grade_s2' => $this->maths_grade_s2,
            'insurance_status' => $this->insurance_status,
            'insurance_number' => $this->insurance_number,
            'insurance_date' => $this->insurance_date?->format('Y-m-d'),
            'insurance_start_date' => $this->insurance_start_date,
            'insurance_end_date' => $this->insurance_end_date,
            
            // Hierarchical validation
            'validation_status' => $this->validation_status,
            'validated_by_1' => $this->validated_by_1,
            'validated_at_1' => $this->validated_at_1,
            'validated_by_2' => $this->validated_by_2,
            'validated_at_2' => $this->validated_at_2,
            
            'status_history'=>BeneficiaryStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
        ];
    }
}
