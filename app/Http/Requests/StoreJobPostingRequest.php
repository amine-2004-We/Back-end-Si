<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostingRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'position_id' => 'required|exists:position,id',
            'launch_date' => 'required|date',
            'closing_date' => 'required|date|after_or_equal:launch_date',
            'type' => 'required|in:Interne,Externe,Interne et Externe',
            'status' => 'required|in:En préparation,Publié,Clôturé,Annulé',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'project_id.required' => 'Le projet est obligatoire.',
            'project_id.exists' => 'Le projet sélectionné est invalide.',
            'position_id.required' => 'Le poste est obligatoire.',
            'position_id.exists' => 'Le poste sélectionné est invalide.',
            'launch_date.required' => 'La date de lancement est obligatoire.',
            'launch_date.date' => 'La date de lancement doit être une date valide.',
            'closing_date.required' => 'La date de clôture est obligatoire.',
            'closing_date.date' => 'La date de clôture doit être une date valide.',
            'closing_date.after_or_equal' => 'La date de clôture doit être postérieure ou égale à la date de lancement.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type sélectionné est invalide.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
        ];
    }
}
