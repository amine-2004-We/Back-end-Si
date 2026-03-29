<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTraineeCollaboratorForTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisations légères avant validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('training_groups') && is_null($this->input('training_groups'))) {
            $this->merge(['training_groups' => []]);
        }
    }

    public function rules(): array
    {
        $trainingParam = $this->route('training');
        $trainingId = is_object($trainingParam) ? ($trainingParam->id ?? null) : $trainingParam;

        return [
            'collaborator_id' => ['sometimes', 'integer', 'exists:collaborators,id'],

            'insured' => ['sometimes', 'in:false,0'],

            'remarks' => ['sometimes', 'nullable', 'string'],

            'trainings'                          => ['required','array','min:1'],
            'trainings.*.training_id'            => [
                'required','integer',
                Rule::exists('trainings', 'id')
                    ->where(fn($q) => $q->whereIn('training_type', ['continuous','monthly']))
            ],
            'trainings.*.training_evaluation_status'     => ['nullable'],
            'trainings.*.satisfaction_evaluation' => ['nullable'],
            'trainings.*.registered_at'           => ['nullable','date'],
            'training_groups'   => ['sometimes', 'array'],
            'training_groups.*' => [
                'integer',
                'distinct',
                Rule::exists('training_groups', 'id')
                    ->when($trainingId, fn (Rule $rule) =>
                        $rule->where(fn ($q) => $q->where('training_id', $trainingId))
                    ),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'collaborator_id' => 'collaborateur',
            'insured' => 'assuré',
            'remarks' => 'remarques',
            'trainings' => 'informations de formation',
            'trainings.training_evaluation_status' => 'évaluation de la formation',
            'trainings.satisfaction_evaluation' => 'évaluation de satisfaction',
            'trainings.registered_at' => "date d'inscription",
            'training_groups' => 'groupes de formation',
            'training_groups.*' => 'groupe de formation',
        ];
    }

  
    public function messages(): array
    {
        return [
            'collaborator_id.sometimes' => 'Le champ :attribute est facultatif.',
            'collaborator_id.integer'   => 'Le :attribute doit être un identifiant numérique.',
            'collaborator_id.exists'    => 'Le :attribute est introuvable.',

            'insured.in' => "Le champ :attribute doit être faux dans ce contexte.",

            'remarks.string'   => 'Les :attribute doivent être une chaîne de caractères.',
            'remarks.nullable' => 'Les :attribute peuvent être vides.',

            'trainings.sometimes' => 'Le bloc ":attribute" est facultatif.',
            'trainings.array'     => 'Le bloc ":attribute" doit être un objet/tabl͟eau valide.',

            'trainings.training_evaluation.integer' => "L':attribute doit être un entier.",
            'trainings.training_evaluation.between' => "L':attribute doit être compris entre :min et :max.",

            'trainings.satisfaction_evaluation.integer' => "L':attribute doit être un entier.",
            'trainings.satisfaction_evaluation.between' => "L':attribute doit être compris entre :min et :max.",

            'trainings.registered_at.date' => "La :attribute doit être une date valide (YYYY-MM-DD).",

            'training_groups.sometimes' => 'Les :attribute sont facultatifs.',
            'training_groups.array'     => 'Les :attribute doivent être une liste.',
            'training_groups.*.integer'  => 'Chaque :attribute doit être un identifiant numérique.',
            'training_groups*.distinct'  => 'Les :attribute ne doivent pas contenir de doublons.',
            'training_groups.*.exists'   => 'Un :attribute fourni n’appartient pas à cette formation ou est introuvable.',
        ];
    }
}
