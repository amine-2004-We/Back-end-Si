<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompetencyGridRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'                   => ['nullable', 'string', 'max:255', 'unique:competency_grids,code'],
            'title'                  => ['required', 'string', 'max:255'],
            'pedagogical_objective'  => ['required', 'string'],
            
            'grid_type'              => ['required', Rule::in(['evaluation', 'impact', 'follow_up'])],
            'grading_scheme'         => ['required', Rule::in(['status', 'scale10', 'scale20'])],
            // 'lifecycle_status'       => ['sometimes', Rule::in(['in_progress', 'validated', 'archived'])],
            
            'instructions'           => ['nullable', 'string'],
            'created_by_id'          => ['prohibited'], // Set automatically by observer
            
            'criteria'                    => ['nullable', 'array'],
            'criteria.*.criterion_id'     => ['required', 'integer', 'exists:competency_criteria,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                    => 'Le titre est requis.',
            'pedagogical_objective.required'    => 'L\'objectif pédagogique est requis.',
            'grid_type.required'                => 'Le type de grille est requis.',
            'grid_type.in'                      => 'Le type de grille doit être : evaluation, impact ou follow_up.',
            'grading_scheme.required'           => 'Le système de notation est requis.',
            'grading_scheme.in'                 => 'Le système de notation doit être : status, scale10 ou scale20.',
            // 'lifecycle_status.in'               => 'Le statut du cycle de vie doit être : in_progress, validated ou archived.',
            'code.unique'                       => 'Ce code existe déjà.',
            'created_by_id.prohibited'          => 'Le créateur est défini automatiquement.',
            'criteria.*.criterion_id.exists'    => 'Un des critères n\'existe pas.',
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
