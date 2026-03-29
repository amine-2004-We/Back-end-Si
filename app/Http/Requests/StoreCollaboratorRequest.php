<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreCollaboratorRequest extends FormRequest
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
            'email' => 'required|string|unique:collaborators,email',
            'password' => 'required|string|min:6',
            'cin' => 'required|string|unique:collaborators,cin|max:255',
            'cnss' => 'nullable|string|unique:collaborators,cnss|max:255',
            'cimr' => 'nullable|string|unique:collaborators,cimr|max:255',
            'insurance_membership_number'=> 'nullable|string|unique:collaborators,insurance_membership_number|max:255',
            'title' => 'required|string',
            'phone' => 'required|string|max:20',
            'rib' => 'required|string|unique:collaborators,rib|max:30',
            'birth_date' => [
                'required',
                'date',
                'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            ],
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
            'projects' => 'required|array',
            'projects.*' => 'exists:projects,id',
        ];
    }
    public function attributes(): array
    {
        return [
            'last_name' => 'Last name',
            'first_name' => 'First name',
            'last_name_ar' => 'Last name (Arabic)',
            'first_name_ar' => 'First name (Arabic)',
            'email' => 'Email address',
            'password' => 'Password',
            'cin' => 'National ID (CIN)',
            'cnss' => 'CNSS number',
            'cimr' => 'CIMR number',
            'insurance_membership_number' => 'Insurance membership number',
            'title' => 'Title',
            'phone' => 'Phone number',
            'rib' => 'Bank account number (RIB)',
            'birth_date' => 'Date of birth',
            'birth_region_id' => 'Birth region',
            'birth_province_id' => 'Birth province',
            'residence_address' => 'Residential address',
            'residence_region_id' => 'Residence region',
            'residence_province_id' => 'Residence province',
            'department_id' => 'Department',
            'position_id' => 'Position',
            'source' => 'Source',
            'entry_date' => 'Entry date',
            'exit_date' => 'Exit date',
            'gross_salary' => 'Gross salary',
            'net_salary' => 'Net salary',
            'trial_period' => 'Trial period',
            'notice_period' => 'Notice period',
            'total_experience' => 'Total experience',
            'educational_experience' => 'Educational experience',
            'cart' => 'CART allowance',
            'transportation' => 'Transportation allowance',
            'presentation' => 'Presentation allowance',
            'movement' => 'Movement allowance',
            'annual_leave_days' => 'Annual leave days',
            'assigned_region_id' => 'Assigned region',
            'assigned_province_id' => 'Assigned province',
            'education_level' => 'Education level',
            'discipline' => 'Field of study',
            'institution' => 'Educational institution',
            'graduation_date' => 'Graduation date',
            'family_member' => 'Family member',
            'family_relationship' => 'Family relationship',
            'hierarchical_superior' => 'Hierarchical superior',
            'marital_status' => 'Marital status',
            'collaborator_status_id' => 'Collaborator status',
            'contract_type_id' => 'Contract type',
            'contract_status_id' => 'Contract status',
            'photo' => 'Profile photo',
            'projects' => 'Projects',
            'projects.*' => 'Project',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'min' => 'Le champ :attribute doit contenir au moins :min caractères.',
            'email' => 'Veuillez saisir une adresse e-mail valide.',
            'unique' => 'Le champ :attribute est déjà utilisé.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'before_or_equal' => 'Vous devez avoir au moins 18 ans.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'integer' => 'Le champ :attribute doit être un nombre entier.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide ou n’existe pas.',
            'image' => 'Le champ :attribute doit être une image (jpeg, png, jpg, gif).',
            'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
            'array' => 'Le champ :attribute doit être une liste.',
            'projects.*.exists' => 'Chaque projet sélectionné doit être valide.',
            'photo.max' => 'La photo de profil ne doit pas dépasser 2 Mo.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'projects.required' => 'Au moins un projet doit être sélectionné.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser :max caractères.',
            'rib.max' => 'Le RIB ne doit pas dépasser :max caractères.',
        ];
    }


}
