<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_name' => 'required|string|max:255',
            'internal_class_code' => 'nullable|string|max:255',
            'region_id'=>'required|integer|exists:regions,id',
            'unit_id' => 'required|exists:units,id',
            'cycle_id' => 'required|exists:cycles,id',
            'local_pedagogical_coordinator' => 'nullable|exists:collaborators,id',
            'class_status_id' => 'required|exists:class_status,id',
            'note' => 'nullable|string',
            'douar_id'=>'required|integer|exists:douars,id',
            'class_resources' => 'required|array',
        ];
    }

    public function attributes(): array
    {
        return [
            'class_name' => 'Class name',
            'unit_id' => 'Unit',
            'levels_id' => 'Level',
            'cycle_id' => 'Cycle',
            'local_pedagogical_coordinator' => 'Pedagogical coordinator',
            'class_status_id' => 'Class status',
            'note' => 'Note',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'array' => 'Le champ :attribute doit être une liste.',
            'date' => 'Le champ :attribute doit être une date valide.',
        ];
    }
}
