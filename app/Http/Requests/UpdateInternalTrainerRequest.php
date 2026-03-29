<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInternalTrainerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'collaborator_id'          => [
                'sometimes', 
                'integer', 
                'exists:collaborators,id', 
                Rule::unique('internal_trainers', 'collaborator_id')->ignore($this->route('internalTrainer'))->whereNull('deleted_at')
            ],
            'is_available'             => ['sometimes', 'boolean'],
            'interventions_evaluation' => ['sometimes', 'numeric', 'min:0'],
            'remarks'                  => ['nullable', 'string'],
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
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
