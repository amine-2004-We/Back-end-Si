<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'title' => ['required', 'string', 'max:255', Rule::unique('tasks')->where('project_id', $this->project_id)],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'program_type_id'=>['nullable', 'integer', 'exists:program_types,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'phase_id' => ['nullable', 'integer', 'exists:phases,id'],
            'type' => ['nullable', 'string', Rule::in(['Séance pédagogique', 'Réunion', 'Atelier', 'Visite', 'Évaluation', 'Autre', 'Program'])],
            'responsible_collaborator_id' => ['nullable', 'integer', 'exists:collaborators,id'],
            'class_id' => ['nullable', 'integer', 'exists:class,id'],
            'group_ids' => ['nullable', 'array', 'min:1'],
            'group_ids.*' => ['integer', 'exists:groups,id'],
            'expected_start_date' => ['required', 'date'],
            'expected_end_date' => ['required', 'date', 'after_or_equal:expected_start_date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'location_site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'status' => ['required', Rule::in(['Prévue', 'En cours', 'Réalisée', 'Annulée', 'Reportée'])],
            'details' => ['sometimes', 'array'],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:actual_start_date'],
            'field_observations' => ['nullable', 'string'],
            'associated_document' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx', 'max:2048'],
            'implementation_method' => ['nullable', 'string','in:Résultat attendu,livrable'],
            'budget_line_id' => ['nullable', 'integer', 'exists:budget_lines,id'],

        ];

        switch ($type) {
            case 'Séance pédagogique':
                $rules['details.expected_beneficiaries_count'] = ['required', 'integer', 'min:0'];
                break;
            case 'Visite':
                $rules['details.subject'] = ['required', 'string', 'max:255'];
                $rules['details.observed_collaborator_id'] = ['nullable', 'integer', 'exists:collaborators,id'];
                $rules['details.objectives'] = ['required', 'string'];
                break;
            case 'Réunion':
                $rules['details.parent_ids'] = ['nullable', 'array'];
                $rules['details.parent_ids.*'] = ['integer', 'exists:parents,id'];
            case 'Atelier':
                $rules['details.theme'] = ['required', 'string', 'max:255'];
                $rules['details.expected_participants_count'] = ['required', 'integer', 'min:0'];
                $rules['details.objectives'] = ['required', 'string'];
                break;
            case 'Évaluation':
                $rules['details.evaluated_beneficiary_id'] = ['required', 'integer', 'exists:beneficiaires,id'];
                $rules['details.learning_domain'] = ['required', 'string'];
                $rules['details.targeted_competency'] = ['required', 'string'];
                $rules['details.achieved_level'] = ['required', 'string'];
                $rules['details.participation_status'] = ['required', Rule::in(['Réalisée', 'Absent', 'Non Participatif'])];
                break;
        }

        return $rules;
    }
}
