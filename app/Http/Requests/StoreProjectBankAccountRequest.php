<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class StoreProjectBankAccountRequest
 */
class StoreProjectBankAccountRequest extends FormRequest
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
            'rib_iban' => [
                'required',
                'string',
                'min:24',
                'max:34',
                Rule::unique('project_bank_accounts', 'rib_iban')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'bank_id' => 'required|exists:banks,id',
            'agency' => 'nullable|string|max:255',
            'account_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_bank_accounts', 'account_title')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'account_holder_name' => 'required|string|max:255',
            'opening_country' => 'required|string|max:255',
            'opening_date' => 'nullable|string|max:255',
            'supporting_document' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,svg',
            'status' => 'required|string|max:255',
            'comments' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom error messages for validator errors in French.
     */
    public function messages(): array
    {
        return [
            'rib_iban.required' => 'Le RIB/IBAN est obligatoire.',
            'rib_iban.string' => 'Le RIB/IBAN doit être une chaîne de caractères.',
            'rib_iban.min' => 'Le RIB/IBAN doit contenir au moins 24 caractères.',
            'rib_iban.max' => 'Le RIB/IBAN ne peut pas dépasser 34 caractères.',
            'rib_iban.unique' => 'Ce RIB/IBAN existe déjà.',

            'bank.required' => 'La banque est obligatoire.',
            'bank.string' => 'Le nom de la banque doit être une chaîne de caractères.',
            'bank.max' => 'Le nom de la banque ne peut pas dépasser 255 caractères.',

            'agency.string' => "L'agence doit être une chaîne de caractères.",
            'agency.max' => "L'agence ne peut pas dépasser 255 caractères.",

            'account_title.required' => "L'intitulé du compte est obligatoire.",
            'account_title.string' => "L'intitulé du compte doit être une chaîne de caractères.",
            'account_title.max' => "L'intitulé du compte ne peut pas dépasser 255 caractères.",
            'account_title.unique' => "Cet intitulé de compte existe déjà.",

            'account_holder_name.required' => 'Le nom du titulaire est obligatoire.',
            'account_holder_name.string' => 'Le nom du titulaire doit être une chaîne de caractères.',
            'account_holder_name.max' => 'Le nom du titulaire ne peut pas dépasser 255 caractères.',

            'bic_swift.string' => 'Le code BIC/SWIFT doit être une chaîne de caractères.',
            'bic_swift.max' => 'Le code BIC/SWIFT ne peut pas dépasser 255 caractères.',

            'opening_country.required' => "Le pays d'ouverture est obligatoire.",
            'opening_country.string' => "Le pays d'ouverture doit être une chaîne de caractères.",
            'opening_country.max' => "Le pays d'ouverture ne peut pas dépasser 255 caractères.",

            'opening_date.string' => "La date d'ouverture doit être une chaîne de caractères.",

            'supporting_document.file' => 'Le document justificatif doit être un fichier.',
            'supporting_document.max' => 'Le document justificatif ne peut pas dépasser 5 Mo.',
            'supporting_document.mimes' => 'Le document justificatif doit être un fichier de type : pdf, jpg, jpeg, png, svg.',

            'currency.required' => 'La devise est obligatoire.',
            'currency.string' => 'La devise doit être une chaîne de caractères.',
            'currency.max' => 'La devise ne peut pas dépasser 4 caractères.',

            'status.required' => 'Le statut est obligatoire.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.max' => 'Le statut ne peut pas dépasser 255 caractères.',

            'comments.string' => 'Le champ commentaire doit être une chaîne de caractères.',
            'comments.max' => 'Le commentaire ne peut pas dépasser 255 caractères.',
        ];
    }
}
