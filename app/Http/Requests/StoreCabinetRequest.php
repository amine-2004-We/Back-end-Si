<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCabinetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Set to true to allow anyone to make this request.
        // You can add authorization logic here if needed.
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
            'name'             => ['required', 'string', 'max:255', Rule::unique('cabinets', 'name')],
            'responsible_name'   => ['required', 'string', 'max:255'],
            'contact_email'    => ['required', 'string', 'email', 'max:255'],
            'contact_phone'    => ['required', 'string', 'max:255'],
            'address'          => ['required', 'string'],
            'place_id'          => ['nullable', 'exists:places,id'],
            'contracts'        => ['nullable', 'array'],
            'contracts.*'      => ['file', 'mimes:pdf,doc,docx', 'max:5120'],
            'notes'            => ['nullable', 'string'],
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
            'name.required' => 'Le nom du cabinet est requis.',
            'name.string'   => 'Le nom du cabinet doit être une chaîne de caractères.',
            'name.max'      => 'Le nom du cabinet ne doit pas dépasser 255 caractères.',
            'name.unique'   => 'Ce nom de cabinet est déjà utilisé.',

            'responsible_name.required' => 'Le responsable est requis.',
            'responsible_id.integer'  => 'L’identifiant du responsable doit être un entier.',
            'responsible_id.exists'   => 'Le responsable sélectionné est introuvable.',

            'contact_email.required' => 'L’email de contact est requis.',
            'contact_email.email'    => 'L’email de contact doit être une adresse email valide.',
            'contact_email.max'      => 'L’email de contact ne doit pas dépasser 255 caractères.',

            'contact_phone.required' => 'Le téléphone de contact est requis.',
            'contact_phone.string'   => 'Le téléphone de contact doit être une chaîne de caractères.',
            'contact_phone.max'      => 'Le téléphone de contact ne doit pas dépasser 255 caractères.',

            'address.required' => 'L\'adresse est requise.',
            'address.string'   => 'L\'adresse doit être une chaîne de caractères.',

            'contracts.*.file' => 'Le contrat doit être un fichier valide (pdf, doc, docx).',
            'contracts.*.mimes' => 'Le contrat doit être de type : pdf, doc, docx.',
            'contracts.*.max' => 'Le contrat ne doit pas dépasser 5 Mo.',
        ];
    }
}
