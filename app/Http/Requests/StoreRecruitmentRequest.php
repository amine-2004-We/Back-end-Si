<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecruitmentRequest extends FormRequest
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
            'department_id' => 'required|exists:departements,id',
            'position_id' => 'sometimes|nullable|exists:position,id',
            'number_of_positions' => 'required|integer|min:1',
            'recruitment_reason' => 'nullable|string|in:Remplacement,Création de poste',
            'required_skills' => 'nullable|string',
            'desired_start_date' => 'nullable|date',
            'status' => 'nullable|string|in:En attente,Validée,Rejeté',
            'replaced_collaborator_id' => 'sometimes|nullable|exists:collaborators,id',
            'replacement_reason' => 'sometimes|nullable|string',
            'exit_date' => 'sometimes|nullable|date',
            'province_id' => 'sometimes|nullable|exists:provinces,id',
            'project_id' => 'sometimes|nullable|exists:projects,id',

        ];
    }
    public function messages(): array
    {
        return [
            'department_id.required' => 'Le département est requis.',
            'department_id.exists' => 'Le département sélectionné est invalide.',
            'position_id.exists' => 'Le poste sélectionné est invalide.',
            'number_of_positions.required' => 'Le nombre de postes est requis.',
            'number_of_positions.integer' => 'Le nombre de postes doit être un entier.',
            'number_of_positions.min' => 'Le nombre de postes doit être au moins 1.',
            'recruitment_reason.required' => 'La raison du recrutement est requise.',
            'recruitment_reason.in' => 'La raison du recrutement doit être "Remplacement" ou "Création de poste".',
            'desired_start_date.date' => 'La date de début souhaitée doit être une date valide.',
            'status.in' => 'Le statut doit  être "En attente", "Validée" ou "Rejeté".',
        ];
    }
}
