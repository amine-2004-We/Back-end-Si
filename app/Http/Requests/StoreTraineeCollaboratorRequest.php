<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Training;
use App\Models\TrainingGroup;

class StoreTraineeCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['insured' => false]);
        // No more trainings merge
    }

    public function rules(): array
    {
        $trainingGroupsTable = (new TrainingGroup)->getTable();

        return [
            'collaborator_id' => ['required','integer','exists:collaborators,id'],
            'insured'         => [Rule::in([false, 0, '0', 'false', null])],
            'remarks'         => ['nullable','string'],
            // removed trainings block entirely

            // Make groups optional and nullable (array if present)
            'training_groups'   => ['nullable','array'],
            'training_groups.*' => ['integer','distinct', Rule::exists($trainingGroupsTable, 'id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // since trainings is removed, no extra validation here
        });
    }

    public function attributes(): array
    {
        return [
            'collaborator_id' => 'collaborateur',
            'insured'         => 'assuré',
            'remarks'         => 'remarques',
            'training_groups'   => 'groupes de formation',
            'training_groups.*' => 'groupe de formation',
        ];
    }

    public function messages(): array
    {
        return [
            'collaborator_id.required' => 'Le :attribute est requis.',
            'collaborator_id.integer'  => 'Le :attribute doit être un identifiant numérique.',
            'collaborator_id.exists'   => 'Le :attribute est introuvable.',

            'insured.in' => "Le champ :attribute doit toujours être faux dans ce contexte.",
            'remarks.string' => 'Les :attribute doivent être une chaîne de caractères.',
            'training_groups.array'             => 'Les :attribute doivent être une liste.',
            'training_groups.*.integer'         => 'Chaque :attribute doit être un identifiant numérique.',
            'training_groups.*.distinct'        => 'Les :attribute ne doivent pas contenir de doublons.',
            'training_groups.*.exists'          => 'Un :attribute fourni est introuvable.',
        ];
    }
}
