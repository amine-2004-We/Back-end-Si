<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\EvaluationStatusOperation;

class UpdateEvaluationOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'beneficiary_id' => ['sometimes', 'exists:beneficiaires,id'],
            'session_id' => ['sometimes', 'exists:task_pedagogical_sessions,id'],
            'evaluator' => ['sometimes', 'exists:collaborators,id'],
            'comment' => ['nullable', 'string'],
            'evaluation_status_operation' => ['sometimes', new Enum(EvaluationStatusOperation::class)],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
            'created_by' => ['sometimes', 'exists:users,id'],
            'criteria_scores' => ['nullable', 'array'],
            'criteria_scores.*.grid_id' => ['required_with:criteria_scores', 'exists:evaluation_grid_operations,id'],
            'criteria_scores.*.criteria_id' => ['required_with:criteria_scores', 'exists:evaluation_criteria_operations,id'],
            'criteria_scores.*.score' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
