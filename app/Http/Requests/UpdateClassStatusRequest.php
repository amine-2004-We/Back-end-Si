<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_status_value' => 'required|string|in:Opérationnel,En arrêt,Résilié,Clôturé,Pérennisé',
            'status_change_date' => 'nullable|date',
            'status_change_reason' => 'nullable|string|max:1000',
            'perpetuation_project_id' => 'nullable|integer|exists:project,id',
        ];
    }

    public function messages(): array
    {
        return [
            'class_status_value.required' => 'Le statut de la classe est obligatoire.',
            'class_status_value.in' => 'Le statut doit être l\'un des suivants: Opérationnel, En arrêt, Résilié, Clôturé, Pérennisé.',
            'status_change_date.date' => 'La date de changement de statut doit être une date valide.',
            'status_change_reason.max' => 'Le motif ne doit pas dépasser 1000 caractères.',
            'perpetuation_project_id.exists' => 'Le projet de pérennisation sélectionné n\'existe pas.',
        ];
    }
}
