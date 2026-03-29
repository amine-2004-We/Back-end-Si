<?php

namespace App\Http\Requests;

use App\Enums\TrainingModuleFormat;
use App\Enums\TrainingModuleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'                   => ['sometimes', 'string', 'max:255', Rule::unique('modules', 'title')->ignore($this->route('module'))],
            'pedagogical_objectives'  => ['sometimes', 'string'],
            'training_id'             => ['sometimes', 'integer', 'exists:trainings,id'],
            'trainer_id'              => ['nullable', 'integer', 'exists:trainers,id'],
            'competency_grid_id'      => ['nullable', 'integer', 'exists:competency_grids,id'],
            'total_duration'          => ['sometimes', 'numeric', 'min:0'],
            'formation_type'          => ['sometimes', 'nullable', Rule::in(TrainingModuleFormat::values())],
            'pedagogical_supports'    => ['sometimes', 'nullable', 'array'],
            'evaluation_planned'      => ['sometimes', 'boolean'],
            'status'                  => ['sometimes', Rule::in(TrainingModuleStatus::values())],
            'created_by_id'           => ['prohibited'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du module est requis.',
            'title.unique'   => 'Ce titre de module est déjà utilisé.',
            'pedagogical_objectives.required' => 'Les objectifs pédagogiques sont requis.',
            'training_id.required' => 'La formation associée est requise.',
            'training_id.exists'   => 'La formation sélectionnée est introuvable.',
            'trainer_id.exists'    => 'Le formateur sélectionné est introuvable.',
            'competency_grid_id.exists' => 'La grille de compétences sélectionnée est introuvable.',
            'total_duration.required' => 'La durée totale est requise.',
            'total_duration.numeric'  => 'La durée totale doit être un nombre.',
            'evaluation_planned.boolean'  => 'Le champ "évaluation prévue" doit être vrai ou faux.',
            'formation_type.in' => 'Le type de formation sélectionné est invalide.',
            'status.in'         => 'Le statut sélectionné est invalide.',
        ];
    }
}
