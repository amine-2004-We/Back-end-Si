<?php

namespace App\Http\Requests;

use App\Enums\TrainingModuleFormat;
use App\Enums\TrainingModuleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreModuleRequest extends FormRequest
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
            'title'                   => ['required', 'string', 'max:255', 'unique:modules,title'],
            'pedagogical_objectives'  => ['required', 'string'],
            'training_id'             => ['required', 'integer', 'exists:trainings,id'],
            'trainer_id'              => ['nullable', 'integer', 'exists:trainers,id'],
            'competency_grid_id'      => ['nullable', 'integer', 'exists:competency_grids,id'],
            'total_duration'          => ['required', 'numeric', 'min:0'],
            'formation_type'          => ['nullable', Rule::in(TrainingModuleFormat::values())],
            'pedagogical_supports'    => ['nullable', 'array'], // Assuming JSON is sent as an array
            'evaluation_planned'      => ['required', 'boolean'],
            'status'                  => ['sometimes', Rule::in(TrainingModuleStatus::values())],
            'created_by_id'           => ['prohibited'], // Should be set by observer or service
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
            'evaluation_planned.required' => 'Le champ "évaluation prévue" est requis.',
            'evaluation_planned.boolean'  => 'Le champ "évaluation prévue" doit être vrai ou faux.',
            'formation_type.in' => 'Le type de formation sélectionné est invalide.',
            'status.in'         => 'Le statut sélectionné est invalide.',
        ];
    }
}
