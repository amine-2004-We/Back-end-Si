<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // $supplierTypes = config('suppliers.types.types');
        // $legalStatuses = config('suppliers.legal_statuses.legal_statuses');
        // $countryCodes = config('countries.codes');

        return [
            'company_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'supplier_type' => ['required', 'string'],
            'business_sector' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:suppliers,email,NULL,id,deleted_at,NULL'],
            'contact_people' => ['sometimes', 'nullable', 'string'],
            'contact_people.*.first_name' => ['nullable', 'string', 'max:255'],
            'contact_people.*.last_name' => ['nullable', 'string', 'max:255'],
            'contact_people.*.position' => ['nullable', 'string', 'max:255'],
            'contact_people.*.email' => ['nullable', 'email', 'max:255'], 
            'contact_people.*.phone' => ['nullable', 'string', 'max:20'],
            'contact_people.*.address' => ['nullable', 'string', 'max:500'],            
            'legal_status' => ['required', 'string'],
            'tax_id' => ['required', 'string', 'unique:suppliers,tax_id', 'max:255'],
            'commercial_register_number' => ['nullable', 'string', 'max:255'],
            'rib' => ['nullable', 'string', 'max:255'],
            'supporting_documents' => ['nullable', 'array', 'max:5'],
            'supporting_documents.*' => ['mimes:pdf,jpg,png', 'max:10240'],

        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'La raison sociale est obligatoire.',
            'company_name.string' => 'La raison sociale doit être une chaîne de caractères.',
            'company_name.max' => 'La raison sociale ne doit pas dépasser 255 caractères.',

            'trade_name.string' => 'Le nom commercial doit être une chaîne de caractères.',
            'trade_name.max' => 'Le nom commercial ne doit pas dépasser 255 caractères.',

            'supplier_type.required' => 'Le type de fournisseur est obligatoire.',
            'supplier_type.in' => 'Le type de fournisseur sélectionné n\'est pas valide.',

            'business_sector.required' => 'Le domaine d\'activité est obligatoire.',
            'business_sector.string' => 'Le domaine d\'activité doit être une chaîne de caractères.',
            'business_sector.max' => 'Le domaine d\'activité ne doit pas dépasser 255 caractères.',

            'address.required' => 'L\'adresse est obligatoire.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 500 caractères.',

            'city.required' => 'La ville est obligatoire.',
            'city.string' => 'La ville doit être une chaîne de caractères.',
            'city.max' => 'La ville ne doit pas dépasser 255 caractères.',

            'country.required' => 'Le pays est obligatoire.',
            'country.string' => 'Le pays doit être une chaîne de caractères.',
            'country.in' => 'Le pays sélectionné n\'est pas valide.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',

            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.string' => 'L\'adresse e-mail doit être une chaîne de caractères.',
            'email.email' => 'L\'adresse e-mail doit être une adresse e-mail valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un fournisseur actif.',

            'contact_person_role.string' => 'La qualité de la personne doit être une chaîne de caractères.',
            'contact_person_role.max' => 'La qualité de la personne ne doit pas dépasser 255 caractères.',
            'contact_people.*.email.email' => "L'adresse email du contact n'est pas valide.",
            'contact_people.*.email.max' => "L'adresse email du contact ne doit pas dépasser 255 caractères.",


            'legal_status.required' => 'Le statut juridique est obligatoire.',
            'legal_status.in' => 'Le statut juridique sélectionné n\'est pas valide.',

            'tax_id.required' => 'L\'identifiant fiscal est obligatoire.',
            'tax_id.string' => 'L\'identifiant fiscal doit être une chaîne de caractères.',
            'tax_id.unique' => 'Cet identifiant fiscal est déjà utilisé.',
            'tax_id.max' => 'L\'identifiant fiscal ne doit pas dépasser 255 caractères.',

            'commercial_register_number.string' => 'Le registre de commerce doit être une chaîne de caractères.',
            'commercial_register_number.max' => 'Le registre de commerce ne doit pas dépasser 255 caractères.',

            'bank_account_id.exists' => 'Le compte bancaire sélectionné n\'existe pas.',

            'supporting_documents.array' => 'Les documents justificatifs doivent être un tableau de fichiers.',
            'supporting_documents.max' => 'Vous ne pouvez pas télécharger plus de :max fichiers.',

            'supporting_documents.*.mimes' => 'Les documents justificatifs doivent être de type PDF, JPG ou PNG.',
            'supporting_documents.*.max' => 'Le document justificatif ne doit pas dépasser 10 Mo.',
        ];
    }
}
