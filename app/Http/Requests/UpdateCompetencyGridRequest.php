<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompetencyGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'                   => ['sometimes', 'string', 'max:255', Rule::unique('competency_grids', 'code')->ignore($this->route('competencyGrid'))],
            'title'                  => ['sometimes', 'string', 'max:255'],
            'pedagogical_objective'  => ['sometimes', 'string'],

            'grid_type'              => ['sometimes', Rule::in(['evaluation', 'impact', 'follow_up'])],
            'grading_scheme'         => ['sometimes', Rule::in(['status', 'scale10', 'scale20'])],
            'lifecycle_status'       => ['sometimes', Rule::in(['in_progress', 'validated', 'archived'])],

            'instructions'           => ['sometimes', 'nullable', 'string'],// Cannot be modified

            'criteria'                    => ['sometimes', 'array'],
            'criteria.*.criterion_id'     => ['sometimes', 'integer', 'exists:competency_criteria,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'criteria.*.criterion_id.exists'    => 'Un des critères n\'existe pas.',
            'criteria.required_with'             => 'Tous les champs des critères sont requis.',
            'code.unique'                        => 'Ce code existe déjà.',
            'grid_type.in'                       => 'Le type de grille doit être : evaluation, impact ou follow_up.',
            'grading_scheme.in'                  => 'Le système de notation doit être : status, scale10 ou scale20.',
            'lifecycle_status.in'                => 'Le statut du cycle de vie doit être : in_progress, validated ou archived.',
            'created_by_id.prohibited'           => 'Le créateur ne peut pas être modifié.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title'                  => 'titre',
            'pedagogical_objective'  => 'objectif pédagogique',
            'grid_type'              => 'type de grille',
            'grading_scheme'         => 'système de notation',
            'lifecycle_status'       => 'statut du cycle de vie',
            'instructions'           => 'instructions',
            'criteria'               => 'critères',
            'criteria.*.criterion_id' => 'critère',
        ];
    }
}
