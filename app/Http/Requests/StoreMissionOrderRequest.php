<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMissionOrderRequest extends FormRequest
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
            'mission_description' => ['required', 'string'],
            'advance_amount' => ['required_if:is_advance_requested,true', 'numeric', 'min:0'],
            'objective' => ['nullable', 'string'],
            'project_id' => ['required', 'exists:projects,id'],
            'collaborator_id' => ['required', 'exists:collaborators,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'mission_type' => ['required', 'in:internal,external'],
            'is_advance_requested' => ['required', 'boolean'],
            'province_id' => ['required', 'exists:provinces,id'],
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
            'mission_description.required' => 'La description de la mission est requise.',
            'mission_description.string' => 'La description de la mission doit être une chaîne de caractères.',

            'objective.string' => 'L\'objectif doit être une chaîne de caractères.',

            'project_id.required' => 'Le projet est requis.',
            'project_id.exists' => 'Le projet sélectionné n\'existe pas.',

            'collaborator_id.required' => 'Le collaborateur est requis.',
            'collaborator_id.exists' => 'Le collaborateur sélectionné n\'existe pas.',

            'advance_amount.required_if' => 'Le montant de l\'avance est requis si la demande d\'avance est cochée.',
            'advance_amount.numeric' => 'Le montant de l\'avance doit être un nombre.',
            'advance_amount.min' => 'Le montant de l\'avance doit être supérieur ou égal à 0.',

            'start_date.required' => 'La date de début est requise.',
            'start_date.date' => 'La date de début doit être une date valide.',

            'end_date.required' => 'La date de fin est requise.',
            'end_date.date' => 'La date de fin doit être une date valide.',
            'end_date.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',

            'mission_type.required' => 'Le type de mission est requis.',
            'mission_type.in' => 'Le type de mission doit être interne ou externe.',

            'status.in' => 'Le statut doit être en attente, approuvé ou refusé.',

            'is_advance_requested.required' => 'La demande d\'avance est requise.',
            'is_advance_requested.boolean' => 'La demande d\'avance doit être vraie ou fausse.',

            'province_id.required' => 'La province est requise.',
            'province_id.exists' => 'La province sélectionnée n\'existe pas.',
        ];
    }
}
