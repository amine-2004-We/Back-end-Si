<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\NiveauAppreciationEnum;
use App\Enums\GridStatusEnum;

class StoreEvaluationGridOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'task_evaluation_id' => ['required', 'exists:task_evaluations,id'],
            'educational_area' => ['required', 'string', 'max:255'],
            'targeted_overall_skill' => ['required', 'string', 'max:255'],
            'sub_skill' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'niveau_appreciation' => ['required', new Enum(NiveauAppreciationEnum::class)],
            'grid_version' => ['nullable', 'string', 'max:255'],
            'grid_status' => ['required', new Enum(GridStatusEnum::class)],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
