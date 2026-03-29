<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaboratorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collaborator_code'=>$this->collaborator_code,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'last_name_ar' => $this->last_name_ar,
            'first_name_ar' => $this->first_name_ar,
            'email' => $this->email,
            'cin' => $this->cin,
            'cnss' => $this->cnss,
            'cimr' => $this->cimr,
            'insurance_membership_number' => $this->insurance_membership_number,
            'title' => $this->title,
            'phone' => $this->phone,
            'rib' => $this->rib,
            'birth_date' => $this->birth_date,
            'birth_region_id'=>$this->birth_region_id,
            'birth_province_id' => $this->birth_province_id,
            'residence_address' => $this->residence_address,
            'residence_region_id'=>$this->residence_region_id,
            'residence_province_id' => $this->residence_province_id,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'source' => $this->source,
            'entry_date' => $this->entry_date,
            'exit_date' => $this->exit_date,
            'gross_salary' => $this->gross_salary,
            'net_salary' => $this->net_salary,
            'trial_period' => $this->trial_period,
            'notice_period' => $this->notice_period,
            'total_experience' => $this->total_experience,
            'educational_experience' => $this->educational_experience,
            'bonuses' => $this->bonuses,
            'annual_leave_days' => $this->annual_leave_days,
            'assigned_region_id'=>$this->assigned_region_id,
            'assigned_province_id' => $this->assigned_province_id,
            'education_level' => $this->education_level,
            'discipline' => $this->discipline,
            'institution' => $this->institution,
            'graduation_date' => $this->graduation_date,
            'family_member' => $this->family_member,
            'family_relationship' => $this->family_relationship,
            'hierarchical_superior' => $this->hierarchical_superior,
            'marital_status' => $this->marital_status,
            'collaborator_status_id' => $this->collaborator_status_id,
            'contract_type_id' => $this->contract_type_id,
            'contract_status_id' => $this->contract_status_id,
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'deleted_at'=>$this->deleted_at? $this->deleted_at->toDateTimeString():null
        ];
    }
}
