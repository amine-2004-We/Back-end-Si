<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExternalTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $trainerId = $this->route('external_trainer')->id;
        return [
            'full_name' => 'sometimes|required|string|max:255',
            'affiliation_type' => ['sometimes', 'required', Rule::in(['Indépendant', 'Via cabinet'])],
            'cabinet_id' => 'nullable|required_if:affiliation_type,Via cabinet|integer|exists:cabinets,id',
            'phone' => 'sometimes|required|string|max:20',
            'email' => ['sometimes', 'required', 'email', Rule::unique('external_trainers')->ignore($trainerId)],
            'cv' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'interventions_evaluation' => 'sometimes|nullable|numeric|min:0|max:5',
            'remarks' => 'sometimes|nullable|string|max:65535',
            'is_available' => 'sometimes|boolean',
        ];
    }
}
