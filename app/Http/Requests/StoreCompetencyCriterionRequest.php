<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetencyCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:savoir,savoir-faire,savoir-etre',
            'scoring_scale' => 'required|string|max:255',
            'display_order' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'required|boolean',
        ];
    }
}
