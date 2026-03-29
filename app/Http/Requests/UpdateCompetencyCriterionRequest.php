<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompetencyCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:savoir,savoir-faire,savoir-etre',
            'scoring_scale' => 'sometimes|required|string|max:255',
            'display_order' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
