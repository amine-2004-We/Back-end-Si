<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['Préparation', 'Mise en œuvre', 'Suivi-évaluation', 'Clôture'])],
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after_or_equal:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            'status' => ['required', 'string', Rule::in(['Prévue', 'En cours', 'Terminée', 'Archivée'])],
            'execution_order' => 'required|integer|min:1',
            'responsible_id' => 'nullable|integer|exists:users,id',
            'comments' => 'nullable|string',
        ];
    }
}