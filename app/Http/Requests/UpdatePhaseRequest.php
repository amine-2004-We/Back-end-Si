<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'sometimes|required|integer|exists:projects,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => ['sometimes', 'required', 'string', Rule::in(['Préparation', 'Mise en œuvre', 'Suivi-évaluation', 'Clôture'])],
            'planned_start_date' => 'sometimes|required|date',
            'planned_end_date' => 'sometimes|required|date|after_or_equal:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            'status' => ['sometimes', 'required', 'string', Rule::in(['Prévue', 'En cours', 'Terminée', 'Archivée'])],
            'execution_order' => 'sometimes|required|integer|min:1',
            'responsible_id' => 'nullable|integer|exists:users,id',
            'comments' => 'nullable|string',
        ];
    }
}