<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobPostingRequest extends FormRequest
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
            //
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'project_id' => 'sometimes|required|exists:projects,id',
            'position_id' => 'sometimes|required|exists:position,id',
            'launch_date' => 'sometimes|required|date',
            'closing_date' => 'sometimes|required|date|after_or_equal:launch_date',
            'type' => 'sometimes|required|in:Interne,Externe,Interne et Externe',
            'status' => 'sometimes|required|in:En préparation,Publié,Clôturé,Annulé',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Le nom doitêtre une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doitêtre une chaîne de caractères.',
            'project_id.exists' => 'Le projet sélectionné est invalide.',
            'position_id.exists' => 'Le poste sélectionné est invalide.',
            'launch_date.date' => 'La date de lancement doit être une date valide.',
            'closing_date.date' => 'La date de clôture doit être une date valide.',
            'closing_date.after_or_equal' => 'La date de clôture doit être postérieure ou égale à la date de lancement.',
            'type.in' => 'Le type sélectionné est invalide.',
            'status.in' => 'Le statut sélectionné est invalide.',
        ];
    }
}
