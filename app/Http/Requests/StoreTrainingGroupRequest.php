<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreTrainingGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255', Rule::unique('training_groups', 'title')->whereNull('deleted_at')],
            'training_id'       => ['required', 'exists:trainings,id'],
            'target_size'       => ['nullable', 'integer', 'min:1'],
            'status'            => ['required', Rule::in(['active', 'closed', 'cancelled'])],
            'remarks'           => ['nullable', 'string'],
            'responsible_id'    => ['required', 'exists:collaborators,id'],

            'participants'          => ['nullable', 'array'],
            'participants.*.id'     => ['required', 'integer'],
            'participants.*.type'   => ['required', 'string', Rule::in(['collaborator', 'candidate', 'external'])],
        ];
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $participants = $this->input('participants', []);

            foreach ($participants as $index => $participant) {
                $id = $participant['id'] ?? null;
                $type = $participant['type'] ?? null;

                if (!$id || !$type) continue;

                $table = match ($type) {
                    'collaborator'   => 'collaborators',
                    'candidate' => 'candidates',
                    'external'  => 'externals',
                    default     => null,
                };

                if ($table && !DB::table($table)->where('id', $id)->exists()) {
                    $validator->errors()->add("participants.{$index}.id", "Le participant de type '{$type}' avec l'ID {$id} est introuvable.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'Le titre du groupe est requis.',
            'title.unique'            => 'Un groupe avec ce titre existe déjà.',
            'training_id.required'    => 'La formation est requise.',
            'training_id.exists'      => 'La formation sélectionnée n\'existe pas.',
            'target_size.integer'     => 'La taille cible doit être un nombre entier.',
            'status.required'         => 'Le statut est requis.',
            'responsible_id.exists'   => 'Le responsable sélectionné n\'existe pas.',
            'participants.array'      => 'Les participants doivent être une liste.',
            'participants.*.id.required'    => 'L\'ID du participant est requis.',
            'participants.*.type.required'  => 'Le type du participant est requis.',
            'participants.*.type.in'        => 'Le type de participant est invalide.',
            'responsible_id.required' => 'Le responsable est requis.',
        ];
    }

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
