<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\InsuranceEnum; // Assuming you have this enum

class StoreAssuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $personneExistsRule = function ($attribute, $value, $fail) {
            $type = $this->input('personne_assuree_type');
            if (!$type || !class_exists($type)) {
                return;
            }
            if (!$type::find($value)) {
                $fail("La personne sélectionnée n'est pas valide.");
            }
        };

        return [
            'personne_assuree_type' => ['required', 'string', Rule::in(['App\Models\External', 'App\Models\Candidate'])],
            'personne_assuree_id' => ['required', 'integer', $personneExistsRule],
            'insurance_type' => ['required', Rule::in(['CNSS', 'AMO', 'Retraite complémentaire'])],
            'insurance_organization' => ['required', 'string', 'max:255'],
            'affiliation_date' => ['required', 'date'],
            'termination_date' => ['nullable', 'date', 'after_or_equal:affiliation_date'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'personne_assuree_type.required' => 'Le type de personne assurée est obligatoire.',
            'personne_assuree_id.required' => 'La personne assurée est obligatoire.',
            'insurance_type.required' => 'Le type d\'assurance est obligatoire.',
            'insurance_organization.required' => 'L\'organisme d\'assurance est obligatoire.',
            'affiliation_date.required' => 'La date d\'affiliation est obligatoire.',
            'termination_date.after_or_equal' => 'La date de cessation doit être après la date d\'affiliation.',
            'comments.max' => 'Les commentaires ne doivent pas dépasser 1000 caractères.',
        ];
    }
}
