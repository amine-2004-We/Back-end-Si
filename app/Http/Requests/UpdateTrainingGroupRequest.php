<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UpdateTrainingGroupRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $trainingGroupId = $this->route('trainingGroup');

        return [
            'title'             => ['sometimes', 'string', 'max:255', Rule::unique('training_groups', 'title')->whereNull('deleted_at')->ignore($trainingGroupId)],
            'training_id'       => ['sometimes', 'exists:trainings,id'],
            'target_size'       => ['nullable', 'integer', 'min:1'],
            'status'            => ['sometimes', Rule::in(['active', 'closed', 'cancelled'])],
            'remarks'           => ['nullable', 'string'],
            'responsible_id'    => ['nullable', 'exists:collaborators,id'],

            // --- NOUVELLES RÈGLES DE VALIDATION POUR LES PARTICIPANTS ---
            'participants'          => ['sometimes', 'array'],
            'participants.*.id'     => ['required_with:participants', 'integer'],
            'participants.*.type'   => ['required_with:participants', 'string', Rule::in(['collaborator', 'candidate', 'external'])],
        ];
    }

    /**
     * Configure l'instance du validateur.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('participants')) {
                return;
            }

            $participants = $this->input('participants', []);

            foreach ($participants as $index => $participant) {
                $id = $participant['id'] ?? null;
                $type = $participant['type'] ?? null;

                if (!$id || !$type) continue;

                $table = match ($type) {
                    'collaborator' => 'collaborators',
                    'candidate'    => 'candidates',
                    'external'     => 'externals',
                    default        => null,
                };

                if ($table && !DB::table($table)->where('id', $id)->exists()) {
                    $validator->errors()->add("participants.{$index}.id", "Le participant de type '{$type}' avec l'ID {$id} est introuvable.");
                }
            }
        });
    }

    /**
     * Obtient les messages d'erreur personnalisés pour les règles de validation.
     */
    public function messages(): array
    {
        return [
            'title.unique'            => 'Un groupe avec ce titre existe déjà.',
            'training_id.exists'      => 'La formation sélectionnée n\'existe pas.',
            'target_size.integer'     => 'La taille cible doit être un nombre entier.',
            'status.in'               => 'Le statut doit être actif, fermé ou annulé.',
            'responsible_id.exists'   => 'Le responsable sélectionné n\'existe pas.',
            'participants.array'      => 'Les participants doivent être une liste.',
            'participants.*.id.required_with'    => 'L\'ID du participant est requis.',
            'participants.*.type.required_with'  => 'Le type du participant est requis.',
            'participants.*.type.in'        => 'Le type de participant est invalide.',
        ];
    }

    /**
     * Obtient les noms d'attributs personnalisés pour les erreurs de validation.
     */
    public function attributes(): array
    {
        return [
            'title'             => 'titre du groupe',
            'training_id'       => 'formation',
            'target_size'       => 'taille cible',
            'status'            => 'statut',
            'remarks'           => 'remarques',
            'responsible_id'    => 'responsable',
            'participants'      => 'participants',
            'participants.*.id'   => 'identifiant du participant',
            'participants.*.type' => 'type du participant',
        ];
    }
}
