<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('collaborators', 'email')->ignore($this->route('collaborator')),
            ],
            'cin' => [
                'required',
                'string',
                'max:255',
                Rule::unique('collaborators', 'cin')->ignore($this->route('collaborator')),
            ],
            'cnss' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('collaborators', 'cnss')->ignore($this->route('collaborator')),
            ],
            'cimr' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('collaborators', 'cimr')->ignore($this->route('collaborator')),
            ],
            'insurance_membership_number'=> [
                'nullable',
                'string',
                'max:255',
                Rule::unique('collaborators', 'insurance_membership_number')->ignore($this->route('collaborator')),
            ],
            'title' => 'required|string',
            'phone' => 'required|string|max:20',
            'rib' => 'required|string|max:30',
            'birth_date' => 'required|date',
            'birth_region_id' => 'required|exists:regions,id',
            'birth_province_id' => 'required|exists:provinces,id',
            'residence_address' => 'required|string',
            'residence_region_id' => 'required|exists:regions,id',
            'residence_province_id' => 'required|exists:provinces,id',
            'department_id' => 'required|exists:departements,id',
            'position_id' => 'required|exists:position,id',
            'source' => 'required|string|max:255',
            'entry_date' => 'required|date',
            'exit_date' => 'nullable|date',
            'gross_salary' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'trial_period' => 'required|integer|min:0',
            'notice_period' => 'required|integer|min:0',
            'total_experience' => 'nullable|integer|min:0',
            'educational_experience' => 'nullable|integer|min:0',
            'cart' => 'nullable|numeric|min:0',
            'transportation' => 'nullable|numeric|min:0',
            'presentation' => 'nullable|numeric|min:0',
            'movement' => 'nullable|numeric|min:0',
            'annual_leave_days' => 'required|integer|min:0',
            'assigned_region_id' => 'required|exists:regions,id',
            'assigned_province_id' => 'required|exists:provinces,id',
            'education_level' => 'nullable|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'graduation_date'=> 'nullable|date',
            'family_member' => 'nullable|string|max:255',
            'family_relationship' => 'nullable|string|max:255',
            'hierarchical_superior' => 'nullable|exists:collaborators,id',
            'marital_status' => 'required|string',
            'collaborator_status_id' => 'required|exists:collaborator_status,id',
            'contract_type_id' => 'required|exists:contract_types,id',
            'contract_status_id' => 'required|exists:contract_status,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'projects' => 'nullable|array',
            'projects.*' => 'exists:projects,id',
        ];
    }
}
