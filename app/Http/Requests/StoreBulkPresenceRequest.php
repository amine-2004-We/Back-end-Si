<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkPresenceRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'event_date' => ['required', 'date'],
            'declared_by' => ['required', 'integer', 'exists:users,id'],
            'participants' => ['required', 'array'],

            'participants.*.personable_id' => ['required', 'integer'],
            'participants.*.personable_type' => ['required', 'string'],
            
            'participants.*.status' => ['required', 'string', Rule::in(['Présent', 'Absent', 'En retard', 'Excusé'])],
            'participants.*.arrival_time' => ['nullable', 'date_format:H:i', 'required_if:participants.*.status,En retard'],
            'participants.*.justification' => ['nullable', 'string', 'max:255'],
            'participants.*.observations' => ['nullable', 'string'],
        ];
    }
}