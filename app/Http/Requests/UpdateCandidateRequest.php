<?php

namespace App\Http\Requests;

use App\Enums\CandidateSourcesEnum;
use App\Enums\CandidateStatusEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidateRequest extends FormRequest
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
            'title' => 'sometimes|required|string',
            'last_name' => 'sometimes|required|string',
            'first_name' => 'sometimes|required|string',
            'last_name_ar' => 'sometimes|nullable|string',
            'first_name_ar' => 'sometimes|nullable|string',
            'phone' => 'sometimes|required|string',
             'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('candidates', 'email')->ignore($this->route('candidate')),
            ],
            'cin' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('candidates', 'cin')->ignore($this->route('candidate')),
            ],
            'cnss' => [
                'nullable',
                'string',
                Rule::unique('candidates', 'cnss')->ignore($this->route('candidate')),
            ],
            'rib' => [
                'nullable',
                'required',
                'string',
                'max:30',
                Rule::unique('candidates', 'rib')->ignore($this->route('candidate')),
            ],
            'birth_date' => [
                'sometimes',
                'required',
                'date',
                'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
            ],
            'job_posting_id' => 'sometimes|nullable|exists:job_postings,id',
            'source' => ['sometimes', 'required', Rule::in(CandidateSourcesEnum::values())],
            'status' => ['sometimes', 'required', Rule::in(CandidateStatusEnum::values())],
            'number_of_children' => 'nullable|integer|min:0',
            'birth_region_id' => 'sometimes|required|exists:regions,id',
            'birth_province_id' => 'sometimes|required|exists:provinces,id',
            'residence_address' => 'sometimes|required|string',
            'residence_region_id' => 'sometimes|required|exists:regions,id',
            'residence_province_id' => 'sometimes|required|exists:provinces,id',
            'total_experience' => 'sometimes|nullable|integer|min:0',
            'educational_experience' => 'sometimes|nullable|integer|min:0',
            'education_level' => 'sometimes|nullable|string|max:255',
            'discipline' => 'sometimes|nullable|string|max:255',
            'institution' => 'sometimes|nullable|string|max:255',
            'graduation_date'=> 'sometimes|nullable|date',
            'marital_status' => 'nullable|required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',


        ];
    }

    public function messages(): array
    {
        return [
            // Custom error messages in french
            'title.required' => 'Le champ civilité est obligatoire.',
            'last_name.required' => 'Le champ nom est obligatoire.',
            'first_name.required' => 'Le champ prénom est obligatoire.',
            'phone.required' => 'Le champ téléphone est obligatoire.',
            'email.required' => 'Le champ email est obligatoire.',
            'email.email' => "Le champ email doit être une adresse email valide.",
            'email.unique' => "L'adresse email est déjà utilisée.",
            'cin.required' => 'Le champ CIN est obligatoire.',
            'cin.unique' => 'Le CIN est déjà utilisé.',
            'cnss.unique' => 'Le CNSS est déjà utilisé.',
            'rib.required' => 'Le champ RIB est obligatoire.',
            'birth_date.required' => 'Le champ date de naissance est obligatoire.',
            'birth_date.date' => 'Le champ date de naissance doit être une date valide.',
            'birth_date.before_or_equal' => 'Le candidat doit avoir au moins 18 ans.',
            'residence_address.required' => 'Le champ adresse de résidence est obligatoire.',
            'job_posting_id.exists' => "L'offre d'emploi sélectionnée est invalide.",
            'source.in' => "La source du candidat sélectionnée est invalide.",
            'status.in' => "Le statut du candidat sélectionné est invalide.",
            'number_of_children.integer' => 'Le nombre d\'enfants doit être un nombre entier.',
            'number_of_children.min' => 'Le nombre d\'enfants ne peut pas être négatif.',
            'birth_region_id.required' => 'Le champ région de naissance est obligatoire.',
            'birth_region_id.exists' => 'La région de naissance sélectionnée est invalide.',
            'birth_province_id.required' => 'Le champ province de naissance est obligatoire.',
            'birth_province_id.exists' => 'La province de naissance sélectionnée est invalide.',
            'residence_region_id.required' => 'Le champ région de résidence est obligatoire.',
            'residence_region_id.exists' => 'La région de résidence sélectionnée est invalide.',
            'residence_province_id.required' => 'Le champ province de résidence est obligatoire.',
            'residence_province_id.exists' => 'La province de résidence sélectionnée est invalide.',
            'total_experience.integer' => "L'expérience totale doit être un nombre entier.",
            'total_experience.min' => "L'expérience totale ne peut pas être négative.",
            'educational_experience.integer' => "L'expérience éducative doit être un nombre entier.",
            'educational_experience.min' => "L'expérience éducative ne peut pas être négative.",
            'education_level.string' => 'Le niveau d\'éducation doit être une chaîne de caractères.',
            'education_level.max' => 'Le niveau d\'éducation ne peut pas dépasser 255 caractères.',
            'discipline.string' => 'La discipline doit être une chaîne de caractères.',
            'discipline.max' => 'La discipline ne peut pas dépasser 255 caractères.',
            'institution.string' => 'L\'établissement doit être une chaîne de caractères.',
            'institution.max' => 'L\'établissement ne peut pas dépasser 255 caractères.',
            'graduation_date.date' => 'La date d\'obtention doit être une date valide.',
            'marital_status.required' => 'Le champ situation familiale est obligatoire.',
            'photo.image' => 'Le champ photo doit être une image.',
            'photo.mimes' => 'Le champ photo doit être un fichier de type : :values.',
            'photo.max' => 'Le champ photo ne doit pas dépasser 2 Mo.',
        ];
    }
}
