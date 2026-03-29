<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_id' => ['required', 'integer', 'exists:modules,id'],
            'training_id' => ['required', 'integer', 'exists:trainings,id'],
            'training_group_id' => ['required', 'integer', 'exists:training_groups,id'],
            'animator_type' => ['required', 'string', Rule::in(['App\Models\Collaborator', 'App\Models\ExternalTrainer'])],
            'animator_id' => ['required', 'integer'],
            'session_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'planned_duration_hours' => ['required', 'integer', 'min:1'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'session_type' => ['nullable', 'string', Rule::in(['Theorique', 'Pratique', 'Evaluation'])],
            'presence_registered' => ['required', 'boolean'],
            'observations' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['Planifiée', 'Réalisée', 'Reportée', 'Annulée'])],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }
}