<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_date' => ['sometimes', 'required', 'date'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['Présent', 'Absent', 'En retard', 'Excusé'])],
            'arrival_time' => ['nullable', 'date_format:H:i1'],
            'justification' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
            'declared_by' => ['sometimes', 'required', 'integer', 'exists:users,id'],
        ];
    }
}
