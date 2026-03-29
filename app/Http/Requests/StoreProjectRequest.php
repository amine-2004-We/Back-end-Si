<?php

namespace App\Http\Requests;

use App\Models\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   

    public function rules(): array
    {
         $brouillonId = ProjectStatus::where('name', 'Brouillon')->value('id');
         \Log::info('project status id',['project status id'=>$brouillonId]);
        return [
            'project_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'project_name')->whereNull('deleted_at'),
            ],
      

    'project_abbreviation' => [
        'nullable',
        'string',
        'max:10',
        Rule::unique('projects', 'project_abbreviation')->whereNull('deleted_at'),
        Rule::requiredIf(fn () =>
           request('project_status_id') != $brouillonId
        ),
    ],

          
            // 'project_nature' => ['required', 'string', 'in:Public,Privée,Opérationnel,Expérimental'],
            'project_nature_id' => ['nullable', 'exists:project_types,id', Rule::requiredIf(fn () =>
           request('project_status_id') != $brouillonId
        ),],
            'intervention_axis_id' => ['nullable', 'exists:intervention_axes,id', Rule::requiredIf(fn () =>
            request('project_status_id') != $brouillonId
        ),],
            'project_status_id' => ['nullable', 'exists:project_statuses,id', Rule::requiredIf(fn () =>
           request('project_status_id') != $brouillonId
        ),],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_start_date' => 'nullable|date|after_or_equal:start_date',
            'exercice_comptable' => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:2100'],
            'program_id' => [ 'nullable', 'exists:programs,id', Rule::requiredIf(fn () =>
            request('project_status_id') != $brouillonId
        ),],
            'program_type_id' => [ 'nullable', 'exists:program_types,id', Rule::requiredIf(fn () =>
            request('project_status_id') != $brouillonId
        ),],
           
            // 'partner_amount' => ['nullable', 'numeric', 'min:0'],
           

            'responsible_id' => ['nullable', 'exists:users,id'],


             'region_id' => ['nullable', 'exists:regions,id', Rule::requiredIf(fn () =>
            request('project_status_id') != $brouillonId
        ),],
            'notes' => ['nullable', 'array'],
             'province_id' => [
                'nullable',
                'exists:provinces,id',
                 Rule::exists('provinces', 'id')->where(function ($query) {
                    return $query->where('region_id', $this->region_id);
                }),
                 Rule::requiredIf(fn () =>
            request('project_status_id') != $brouillonId
        ),
            ],

             'partners' => ['nullable', 'array'],
            'partners.*.partner_id' => ['required_with:partners', 'exists:partners,id', 'distinct'],
            'partners.*.partner_role' => ['required_with:partners', 'string'],
            // 'partners.*.partner_contribution' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_name.required' => 'Le nom du projet est requis.',
            'project_name.unique' => 'Ce nom de projet est déjà utilisé.',
            'partner_id.required' => 'Le partenaire source est obligatoire.',
            'region_id.required' => 'La région est obligatoire.',

            // Message personnalisé pour l'erreur de cohérence Région/Province
            'province_id.exists' => 'La province sélectionnée n\'est pas valide ou n\'appartient pas à la région choisie.',
            'province_id.required' => 'La province est obligatoire.',

            'start_date.required' => 'La date de lancement est requise.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure à la date de lancement.',
        ];
    }
}
