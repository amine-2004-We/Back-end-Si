<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecruitmentRequest extends FormRequest
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
            'department_id' => 'sometimes|required|exists:departements,id',
            'position_id' => 'sometimes|required|exists:position,id',
            'number_of_positions' => 'sometimes|required|integer|min:1',
            'recruitment_reason' => 'sometimes|required|string|in:Remplacement,Création de poste',
            'required_skills' => 'nullable|string',
            'desired_start_date' => 'nullable|date',
            'status' => 'sometimes|required|string|in:En attente,Validée,Rejeté',
            'replaced_collaborator_id' => 'nullable|exists:collaborators,id',
            'replacement_reason' => 'nullable|string',
            'exit_date' => 'nullable|date',
            'province_id' => 'nullable|exists:provinces,id',
            'project_id' => 'nullable|exists:projects,id',
            'job_file' => 'sometimes|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }
    public function messages(): array
    {
        return [
            'department_id.required' => 'Le département est requis.',
            'department_id.exists' => 'Le département sélectionné est invalide.',
            'position_id.required' => 'Le poste est requis.',
            'position_id.exists' => 'Le poste sélectionné est invalide.',
            'number_of_positions.required' => 'Le nombre de postes est requis.',
            'number_of_positions.integer' => 'Le nombre de postes doit être un entier.',
            'number_of_positions.min' => 'Le nombre de postes doit être au moins 1.',
            'recruitment_reason.required' => 'La raison du recrutement est requise.',
            'recruitment_reason.in' => 'La raison du recrutement doit être "Remplacement" ou "Création de poste".',
            'desired_start_date.date' => 'La date de début souhaitée doit être une date valide.',
            'status.in' => 'Le statut doit être "En attente", "Validée" ou "Rejeté".',
            'replaced_collaborator_id.exists' => 'Le collaborateur remplacé sélectionné est invalide.',
            'replacement_reason.string' => 'Le motif de remplacement doit être une chaîne de caractères.',
            'exit_date.date' => 'La date de sortie doit être une date valide.',
            'province_id.exists' => 'La province sélectionnée est invalide.',
        ];
    }
}
