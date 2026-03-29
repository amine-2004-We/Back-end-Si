<?php

namespace App\Http\Requests;

use App\Enums\InsuranceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreInsuranceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'collaborator_id' => ['required', 'exists:collaborators,id'],
            'insurance_type' => ['required', Rule::in(InsuranceEnum::values())],
            'insurance_organization' => ['required', 'string', 'max:255'],
            'affiliation_date' => ['required', 'date'],
            'termination_date' => ['nullable', 'date'],
            'comments' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'collaborator_id.required' => 'Le collaborateur est obligatoire.',
            'insurance_type.required' => 'Le type d\'assurance est obligatoire.',
            'insurance_organization.required' => 'L\'organisme d\'assurance est obligatoire.',
            'affiliation_date.required' => 'La date d\'affiliation est obligatoire.',
            'termination_date.date' => 'La date de cessation d\'affiliation doit être une date valide.',
            'comments.max' => 'Les commentaires ne doivent pas dépasser 500 caractères.',
        ];
    }
}
