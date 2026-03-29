<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personable_type' => ['required', 'string', Rule::in(['App\Models\Beneficiary', 'App\Models\Collaborator', 'App\Models\ParentModel'])],
            'personable_id' => ['required', 'integer'],
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['Présent', 'Absent', 'En retard', 'Excusé'])],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'justification' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
            'declared_by' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
