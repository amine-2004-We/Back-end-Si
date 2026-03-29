<?php
// app/Http/Requests/StoreActivityRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'type' => ['required', 'string', Rule::in(['Séance pédagogique', 'Réunion', 'Visite', 'Évaluation','Atelier'])],
            'responsible_collaborator_id' => ['required', 'integer', 'exists:collaborators,id'],
            'group_ids' => ['required', 'array'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'planned_date' => ['nullable', 'date'],
            'actual_date' => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'location_site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'status' => ['required', Rule::in(['Prévue', 'Réalisée', 'Annulée', 'Reportée'])],
            'field_observations' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:2048'],
            'details' => ['required', 'array']
        ];

        switch ($type) {
            case 'Séance pédagogique':
                $rules = array_merge($rules, [
                    'details.expected_beneficiaries_count' => ['required', 'integer', 'min:0'],
                ]);
                break;
            case 'Visite':
                $rules = array_merge($rules, [
                    'details.subject' => ['required', 'string', 'max:255'],
                    'details.observed_collaborator_id' => ['nullable', 'integer', 'exists:collaborators,id'],
                    'details.objectives' => ['required', 'string'],
                    'details.observation_grid_info' => ['nullable', 'string'],
                ]);
                break;
            case 'Réunion':
                 $rules = array_merge($rules, [
                    'details.theme' => ['required', 'string', 'max:255'],
                    'details.expected_participants_count' => ['required', 'integer', 'min:0'],
                    'details.objectives' => ['required', 'string'],
                    'details.distributed_documents' => ['nullable', 'string'],
                ]);
                break;
            case 'Évaluation':
                 $rules = array_merge($rules, [
                    'details.evaluated_beneficiary_id' => ['required', 'integer', 'exists:beneficiaires,id'],
                    'details.learning_domain' => ['required', 'string'],
                    'details.targeted_competency' => ['required', 'string'],
                    'details.achieved_level' => ['required', 'string'],
                    'details.participation_status' => ['required', Rule::in(['Réalisée', 'Absent', 'Non Participatif'])],
                ]);
                break;
        }

        return $rules;
    }
}