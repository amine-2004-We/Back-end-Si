<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_state' => 'required|string|in:Création,Transfert,Relocalisation',
            'transfer_to_project_id' => 'nullable|integer|exists:project,id',
            'relocate_to_class_id' => 'nullable|integer|exists:class,id',
            'relocation_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'class_state.required' => 'L\'état de la classe est obligatoire.',
            'class_state.in' => 'L\'état de la classe doit être l\'un des suivants: Création, Transfert, Relocalisation.',
            'transfer_to_project_id.exists' => 'Le projet de transfert sélectionné n\'existe pas.',
            'relocate_to_class_id.exists' => 'La classe de relocalisation sélectionnée n\'existe pas.',
            'relocation_date.date' => 'La date de relocalisation doit être une date valide.',
        ];
    }
}
