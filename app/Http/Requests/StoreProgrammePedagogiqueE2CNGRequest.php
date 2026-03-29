<?php

namespace App\Http\Requests;

use App\Enums\AtelierE2CNGEnum;
use App\Enums\MatiereE2CNGEnum;
use App\Enums\ProgrammeTypeEnum;
use App\Enums\ProfessionalOptionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProgrammePedagogiqueE2CNGRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if (!$this->has('project_id') || !$this->project_id) {
            $user = auth()->user();
            if ($user && $user->collaborator) {
                // Try to get first project from collaborator's assigned projects
                $firstProject = $user->collaborator->projects()->first();
                
                // If no assigned projects, get the first available project
                if (!$firstProject) {
                    $firstProject = \App\Models\Project::whereNull('deleted_at')->first();
                }
                
                if ($firstProject) {
                    $this->merge(['project_id' => $firstProject->id]);
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'groupe_id' => [
                'nullable',
                'exists:groups,id',
                'required_if:type,' . ProgrammeTypeEnum::FORMATION_BASE->value,
                function ($attribute, $value, $fail) {
                    $type = $this->input('type');
                    $option = $this->input('professional_option');
                    
                    if ($type === ProgrammeTypeEnum::INITIATION_PROFESSIONNELLE->value && 
                        $option === ProfessionalOptionEnum::FORMATION_METIER->value && 
                        empty($value)) {
                        $fail('Le groupe de bénéficiaires est obligatoire pour une formation métier.');
                    }
                },
            ],
            'project_id' => ['nullable', 'exists:projects,id'],
            'type' => ['required', new Enum(ProgrammeTypeEnum::class)],
            'date_prevue' => ['required', 'date'],

            'project_pedagogique' => [
                'nullable',
                'required_if:type,' . ProgrammeTypeEnum::FORMATION_BASE->value,
                new Enum(MatiereE2CNGEnum::class)
            ],

            'professional_option' => [
                'nullable',
                'required_if:type,' . ProgrammeTypeEnum::INITIATION_PROFESSIONNELLE->value,
                new Enum(ProfessionalOptionEnum::class)
            ],

            'metier' => [
                'nullable',
                'required_if:professional_option,' . ProfessionalOptionEnum::FORMATION_METIER->value,
                new Enum(AtelierE2CNGEnum::class) 
            ],

            'ateliers' => [
                'nullable',
                'required_if:professional_option,' . ProfessionalOptionEnum::ATELIER_PRATIQUE->value,
                new Enum(AtelierE2CNGEnum::class)
            ],

            'project_pedagogique_arabe' => ['nullable', 'string', 'max:255'],
            'metier_arabe' => ['nullable', 'string', 'max:255'],
            'ateliers_arabe' => ['nullable', 'array'],
            'observation' => ['nullable', 'string'],
            'date_prevu' => ['nullable', 'date'],
            'date_realisation' => ['nullable', 'date'],
            'real_start_date' => ['nullable', 'date'],
            'real_end_date' => ['nullable', 'date', 'after_or_equal:real_start_date'],
        ];
    }
}