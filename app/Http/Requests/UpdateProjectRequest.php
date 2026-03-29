<?php

namespace App\Http\Requests;

use App\Models\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $brouillonId = ProjectStatus::where('name', 'Brouillon')->value('id');

        if (is_object($project)) {
            $projectId = $project->id;
        } else {
            $projectId = $project ?? $this->route('id');
        }

        // Logic check: Is the current or updated status NOT 'Brouillon'?
        $isNotBrouillon = fn () => $this->input('project_status_id') != $brouillonId;

        return [
            'project_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'project_name')->ignore($projectId)->whereNull('deleted_at')
            ],
            'project_abbreviation' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
                Rule::unique('projects', 'project_abbreviation')->ignore($projectId)->whereNull('deleted_at'),
                Rule::requiredIf($isNotBrouillon),
            ],

            'project_nature_id' => [
                'sometimes',
                'nullable',
                'exists:project_types,id',
                Rule::requiredIf($isNotBrouillon)
            ],
            'intervention_axis_id' => [
                'sometimes',
                'nullable',
                'exists:intervention_axes,id',
                Rule::requiredIf($isNotBrouillon)
            ],

            'region_id' => [
                'sometimes',
                'nullable',
                'exists:regions,id',
                Rule::requiredIf($isNotBrouillon)
            ],
            'province_id' => [
                'sometimes',
                'nullable',
                'exists:provinces,id',
                Rule::exists('provinces', 'id')->where(function ($query) {
                    return $query->where('region_id', $this->region_id);
                }),
                Rule::requiredIf($isNotBrouillon),
            ],

            'project_status_id' => ['sometimes', 'required', 'exists:project_statuses,id'],
            'current_phase' => ['sometimes', 'nullable', 'string', 'in:Pre-projet,Projet'],

            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'actual_start_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],

            'total_budget' => ['sometimes', 'nullable', 'numeric', 'min:0', Rule::requiredIf($isNotBrouillon)],
            'zakoura_contribution' => ['sometimes', 'nullable', 'numeric', 'min:0', Rule::requiredIf($isNotBrouillon)],
            'zakoura_amount' => ['sometimes', 'nullable', 'numeric', 'min:0', Rule::requiredIf($isNotBrouillon)],
            'exercice_comptable' => ['sometimes', 'nullable', 'array'],
            'exercice_comptable.*' => ['string', 'regex:/^\d{4}$/'],
            'analytic_code'=> ['sometimes', 'nullable', 'string'],
            'project_bank_account_id' => ['sometimes', 'nullable', 'exists:project_bank_accounts,id'],

            'responsible_id' => ['sometimes', 'nullable', 'exists:users,id', Rule::requiredIf($isNotBrouillon)],
            'notes' => ['sometimes', 'nullable', 'array'],
            'program_id' => [
                'sometimes',
                'nullable',
                'exists:programs,id',
                Rule::requiredIf($isNotBrouillon)
            ],
            'program_type_id' => [
                'sometimes',
                'nullable',
                'exists:program_types,id',
                Rule::requiredIf($isNotBrouillon)
            ],

            'partners' => ['nullable', 'array'],
            'partners.*.partner_id' => ['required_with:partners', 'exists:partners,id', 'distinct'],
            'partners.*.partner_role' => ['sometimes', 'required_with:partners', 'string'],
            'partners.*.partner_contribution' => ['sometimes', 'required_with:partners', 'numeric', 'min:0'],
            'partners.*.partner_amount' => ['sometimes', 'required_with:partners', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_name.required' => 'Le nom du projet est requis.',
            'project_name.unique' => 'Ce nom de projet est déjà utilisé.',
            'project_abbreviation.required' => 'L\'abréviation est requise pour valider le projet.',
            'project_abbreviation.max' => 'L\'abréviation ne doit pas dépasser 10 caractères.',

            'region_id.required' => 'La région est obligatoire pour valider le projet.',
            'province_id.required' => 'La province est obligatoire pour valider le projet.',
            'province_id.exists' => 'La province sélectionnée n\'est pas valide ou n\'appartient pas à la région choisie.',

            'start_date.required' => 'La date de lancement est requise.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de lancement.',
            'partners.*.partner_id.distinct' => 'Vous ne pouvez pas ajouter deux fois le même partenaire.',
            'partners.*.partner_contribution.numeric' => 'La contribution doit être un nombre valide.',
        ];
    }
}
