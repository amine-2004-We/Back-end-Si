<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCabinetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'string', 'max:255', Rule::unique('cabinets')->ignore($this->cabinet)],
            'responsible_name'   => ['required', 'string', 'max:255'],
            'contact_email'    => ['sometimes', 'string', 'email', 'max:255'],
            'contact_phone'    => ['sometimes', 'string', 'max:255'],
            'address'          => ['sometimes', 'string'],
            'place_id'          => ['nullable', 'exists:places,id'],
            'notes'            => ['nullable', 'string'],
            'average_rating'   => ['sometimes', Rule::in(['Très satisfaisant', 'Moyen', 'Faible'])],

            'contracts'        => ['nullable', 'array'],
            'contracts.*'      => ['file', 'mimes:pdf,doc,docx', 'max:5120'],

            'deleted_contracts'   => ['nullable', 'array'],
            'deleted_contracts.*' => ['string'],
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string'   => 'Le nom du cabinet doit être une chaîne de caractères.',
            'name.max'      => 'Le nom du cabinet ne doit pas dépasser 255 caractères.',
            'name.unique'   => 'Ce nom de cabinet est déjà utilisé.',

            'responsible_name.string'  => 'Le responsable doit être un texte.',
            'responsible_id.exists'   => 'Le responsable sélectionné est introuvable.',

            'contact_email.email'    => 'L’email de contact doit être une adresse email valide.',
            'contact_email.max'      => 'L’email de contact ne doit pas dépasser 255 caractères.',

            'contact_phone.string'   => 'Le téléphone de contact doit être une chaîne de caractères.',
            'contact_phone.max'      => 'Le téléphone de contact ne doit pas dépasser 255 caractères.',

            'address.string'   => 'L\'adresse doit être une chaîne de caractères.',

            'contracts.*.file' => 'Le contrat doit être un fichier valide (pdf, doc, docx).',
            'contracts.*.mimes' => 'Le contrat doit être de type : pdf, doc, docx.',
            'contracts.*.max' => 'Le contrat ne doit pas dépasser 5 Mo.',

            'average_rating.in'       => 'La note moyenne sélectionnée est invalide.',

            'deleted_contracts.array' => 'La liste des contrats à supprimer doit être un tableau.',
        ];
    }
}
