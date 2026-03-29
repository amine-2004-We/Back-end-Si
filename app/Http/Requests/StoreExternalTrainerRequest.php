<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExternalTrainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'affiliation_type' => ['required', Rule::in(['Indépendant', 'Via cabinet'])],
            'cabinet_id' => 'nullable|required_if:affiliation_type,Via cabinet|integer|exists:cabinets,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:external_trainers,email',
            'cv' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:5120',
            'interventions_evaluation' => 'nullable|numeric|min:0|max:5',
            'remarks' => 'nullable|string|max:65535',
            'is_available' => 'sometimes|boolean',
        ];
    }
}
