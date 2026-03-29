<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = $this->route('program')->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('programs')->ignore($programId)],
            'main_objective' => ['required', 'string'],
            'start_date' => ['nullable','required', 'date'],
            'end_date' => ['nullable','required', 'date', 'after_or_equal:start_date'],
      
            'status' => ['required', Rule::in(['Actif', 'Clôturé', 'En pause', 'Archivé'])],
            'operational_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'pedagogical_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'regional_manager_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'observations' => ['nullable', 'string'],
              'program_types'=> ['sometimes','required', 'array', 'min:1'],
            'intervention_axis_id' => ['required', 'exists:intervention_axes,id'],
        ];
    }
}
