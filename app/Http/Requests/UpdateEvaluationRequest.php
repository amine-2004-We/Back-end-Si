<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\EvaluationTypeEnum;
use App\Enums\EvaluationStatusEnum;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'object_project' => ['nullable', 'exists:projects,id'],
            'object_partner' => ['nullable', 'exists:partners,id'],
            'evaluation_type' => ['sometimes', new Enum(EvaluationTypeEnum::class)],
            'evaluation_date' => ['sometimes', 'date'],
            'evaluation_period_start_date' => ['sometimes', 'date'],
            'evaluation_period_end_date' => ['sometimes', 'date', 'after_or_equal:evaluation_period_start_date'],
            'evaluator_id' => ['sometimes', 'exists:collaborators,id'],
            'comment' => ['nullable', 'string'],
            'evaluation_status' => ['sometimes', new Enum(EvaluationStatusEnum::class)],
            'notification' => ['nullable', 'exists:collaborators,id'],
            'criteria_scores' => ['nullable', 'array'],
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }
}
