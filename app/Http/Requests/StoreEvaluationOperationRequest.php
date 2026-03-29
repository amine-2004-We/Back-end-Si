<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\EvaluationStatusOperation;

class StoreEvaluationOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'beneficiary_id' => ['required', 'exists:beneficiaires,id'],
            'session_id' => ['required', 'exists:task_pedagogical_sessions,id'],
            'evaluator' => ['required', 'exists:collaborators,id'],
            'comment' => ['nullable', 'string'],
            'evaluation_status_operation' => ['required', new Enum(EvaluationStatusOperation::class)],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'criteria_scores' => ['nullable', 'array'],
            'criteria_scores.*.grid_id' => ['required_with:criteria_scores', 'exists:evaluation_grid_operations,id'],
            'criteria_scores.*.criteria_id' => ['required_with:criteria_scores', 'exists:evaluation_criteria_operations,id'],
            'criteria_scores.*.score' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
