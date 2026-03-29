<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternalTrainerRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'collaborator_id'          => ['required', 'integer', 'exists:collaborators,id', Rule::unique('internal_trainers', 'collaborator_id')],
            'is_available'             => ['nullable', 'boolean'],
            'interventions_evaluation' => ['nullable', 'numeric', 'min:0'],
            'remarks'                  => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'collaborator_id.required' => 'Le collaborateur est requis.',
            'collaborator_id.integer'  => 'L\'identifiant du collaborateur doit être un entier.',
            'collaborator_id.exists'   => 'Le collaborateur sélectionné est introuvable.',
            'collaborator_id.unique'   => 'Ce collaborateur est déjà désigné comme formateur interne.',

            'is_available.boolean' => 'La disponibilité doit être une valeur booléenne.',

            'interventions_evaluation.numeric' => 'L\'évaluation des interventions doit être un nombre.',
            'interventions_evaluation.min'     => 'L\'évaluation des interventions ne peut pas être négative.',

            'remarks.string' => 'Les remarques doivent être une chaîne de caractères.',
        ];
    }
}
