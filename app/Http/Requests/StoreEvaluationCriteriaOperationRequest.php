<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\GradeLevelEnum;
use App\Enums\EvaluationTypeCriteriaOperationsEnum;
use App\Enums\NiveauAppreciationEnum;

class StoreEvaluationCriteriaOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grid_evaluation_id' => ['required', 'exists:evaluation_grid_operations,id'],
            'title' => ['required', 'string', 'max:255'],
            'criteria_code' => ['nullable', 'string', 'max:255'],
            'grade_level' => ['required', new Enum(GradeLevelEnum::class)],
            'evaluation_type' => ['required', new Enum(EvaluationTypeCriteriaOperationsEnum::class)],
            'weighting' => ['nullable', 'integer', 'min:0'],
            'niveau_appreciation' => ['required', new Enum(NiveauAppreciationEnum::class)],
            'success_indicators' => ['required', 'string', 'max:255'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
