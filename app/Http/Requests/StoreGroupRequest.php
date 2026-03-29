<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:class,id'],
            'educator_id' => ['nullable', 'exists:users,id'],
            'current_headcount' => ['integer', 'min:0'],
            'target_capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,closed,paused'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remarks' => ['nullable', 'string'],
            'level_id'=>['required', 'exists:levels,id']
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
            'name.required' => 'Le nom du groupe est requis.',
            'name.string' => 'Le nom du groupe doit être une chaîne de caractères.',
            'name.max' => 'Le nom du groupe ne doit pas dépasser 255 caractères.',

            'class_id.required' => 'La classe est requise.',
            'class_id.exists' => 'La classe sélectionnée n\'existe pas.',

            'group_type_id.required' => 'Le type de groupe est requis.',
            'group_type_id.exists' => 'Le type de groupe sélectionné n\'existe pas.',

            'educator_id.exists' => 'L\'éducateur sélectionné n\'existe pas.',

            'current_headcount.integer' => 'Le nombre actuel d\'élèves doit être un nombre entier.',
            'current_headcount.min' => 'Le nombre actuel d\'élèves ne peut pas être négatif.',

            'target_capacity.integer' => 'La capacité cible doit être un nombre entier.',
            'target_capacity.min' => 'La capacité cible doit être supérieure à 0.',

            'status.required' => 'Le statut est requis.',
            'status.in' => 'Le statut doit être actif, fermé ou en pause.',

            'start_date.required' => 'La date de début est requise.',
            'start_date.date' => 'La date de début doit être une date valide.',

            'end_date.date' => 'La date de fin doit être une date valide.',
            'end_date.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',

            'remarks.string' => 'Les remarques doivent être une chaîne de caractères.',
        ];
    }
}
