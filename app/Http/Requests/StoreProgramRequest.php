<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:programs,code'],
            'main_objective' => ['required', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            
            'status' => ['required', Rule::in(['Actif', 'Clôturé', 'En pause', 'Archivé'])],
            'operational_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'pedagogical_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'regional_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'observations' => ['required', 'string'],
           'program_types'=> ['required', 'array', 'min:1'],
           
            'intervention_axis_id' => ['required', 'exists:intervention_axes,id'],
        ];
    }
}
