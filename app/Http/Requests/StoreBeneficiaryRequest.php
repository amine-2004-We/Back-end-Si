<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBeneficiaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Masculin', 'Féminin'])],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'place_of_residence' => ['required', 'string', 'max:255'],
            'massar_code' => ['nullable', 'string', 'max:255', 'unique:beneficiaires,massar_code'],
            'nationality' => ['required', Rule::in(['Marocain', 'Étranger'])],
            'address' => ['required', 'string', 'max:1000'],
            'current_school_level_id' => ['required', 'integer', 'exists:levels,id'],
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            // 'status' => ['required', Rule::in(['Inscrit', 'Déperdition', 'Récupération', 'Remplacement','Transfert '])],
            'enrollment_date' => ['required', 'date', 'before_or_equal:today'],
            'radiation_date' => ['nullable', 'date', 'after_or_equal:enrollment_date'],
            'radiation_reason' => ['nullable', Rule::in(['Abandon', 'Mutation', 'Désistement', 'Autre'])],
            'observations' => ['nullable', 'string', 'max:2000'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],

            'parents' => ['nullable', 'array'],
            'parents.existing.*.id' => ['required', 'integer', 'exists:parents,id'],
            'parents.existing.*.legal_role' => ['required', 'string', Rule::in(['father', 'mother', 'legal_guardian'])],

            'parents.new.*.last_name' => ['required', 'string', 'max:255'],
            'parents.new.*.first_name' => ['required', 'string', 'max:255'],
            'parents.new.*.gender' => ['required', Rule::in(['male', 'female'])],
            'parents.new.*.legal_role' => ['required', 'string', Rule::in(['father', 'mother', 'legal_guardian'])],
            'parents.new.*.primary_phone' => ['required', 'string', 'max:20'],
            'parents.new.*.address' => ['required', 'string'],
            'parents.new.*.cin' => ['required', 'string', 'max:50', Rule::unique('parents', 'cin')->whereNull('deleted_at')],

            'country_id' => ['nullable', 'integer', 'exists:countries,id','required_if:nationality,Étranger'],
            'fr_grade_s1' => ['nullable', 'string', 'max:10'],
            'fr_grade_s2_minus_1' => ['nullable', 'string', 'max:10'],
            'fr_grade_s2' => ['nullable', 'string', 'max:10'],
            'maths_grade_s2_minus_1' => ['nullable', 'string', 'max:10'],
            'maths_grade_s1' => ['nullable', 'string', 'max:10'],
            'maths_grade_s2' => ['nullable', 'string', 'max:10'],
            'insurance_status' => ['nullable', 'string', Rule::in(['Non assuré', 'Assuré','En cours'])],
            'is_validated' => ['nullable', 'boolean'],
            'new_status_data' => ['nullable'],
            'new_status_data*.status' => ['required_with:new_status_data', Rule::in(['Inscrit', 'Déperdition', 'Récupération', 'Remplacement','Transfert '])],
            'new_status_data*.status_date' => ['required_with:new_status_data', 'date'],
            'new_status_data*.reason' => ['nullable', 'string', 'max:1000','required_if:new_status_data*.status,Déperdition,Transfert'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'last_name.required' => 'Le nom de famille est obligatoire.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'gender.required' => 'Le sexe est obligatoire.',
            'gender.in' => 'Le sexe sélectionné est invalide.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'date_of_birth.date' => 'La date de naissance doit être une date valide.',
            'date_of_birth.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
            'place_of_residence.required' => 'Le lieu de résidence est obligatoire.',
            'massar_code.unique' => 'Le code Massar existe déjà.',
            'nationality.required' => 'La nationalité est obligatoire.',
            'nationality.in' => 'La nationalité sélectionnée est invalide.',
            'address.required' => 'L\'adresse est obligatoire.',
            'current_school_level_id.required' => 'Le niveau scolaire actuel est obligatoire.',
            'current_school_level_id.exists' => 'Le niveau scolaire sélectionné est invalide.',
            'group_id' => "Le Groupe  du bénéficiaire est obligatoire.",
            'status.required' => 'Le statut du bénéficiaire est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'enrollment_date.required' => 'La date d\'inscription est obligatoire.',
            'enrollment_date.date' => 'La date d\'inscription doit être une date valide.',
            'enrollment_date.before_or_equal' => 'La date d\'inscription ne peut pas être dans le futur.',
            'radiation_date.after_or_equal' => 'La date de radiation doit être postérieure ou égale à la date d\'inscription.',
            'radiation_reason.in' => 'Le motif de radiation sélectionné est invalide.',
            'created_by.exists' => 'L\'utilisateur créateur est invalide.',
        ];
    }
}
