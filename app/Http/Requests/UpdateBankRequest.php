<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('banks', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('id'))
            ],
            'bank_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('banks', 'bank_code')->whereNull('deleted_at')->ignore($this->route('id'))
            ],
            'bic_swift' => 'nullable|string|max:11|min:7',
            'currency' => 'required|string|max:4',
            'country' => 'required|string|max:255',
        ];
    }

    /**
     * Messages de validation personnalisés en français.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la banque est requis.',
            'name.string' => 'Le nom de la banque doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la banque ne peut pas dépasser 255 caractères.',
            'name.unique' => 'Le nom de la banque existe déjà.',

            'bank_code.required' => 'Le code de la banque est requis.',
            'bank_code.string' => 'Le code de la banque doit être une chaîne de caractères.',
            'bank_code.max' => 'Le code de la banque ne peut pas dépasser 255 caractères.',
            'bank_code.unique' => 'Le code de la banque existe déjà.',

            'bic_swift.string' => 'Le code BIC/SWIFT doit être une chaîne de caractères.',
            'bic_swift.max' => 'Le code BIC/SWIFT ne peut pas dépasser 11 caractères.',
            'bic_swift.min' => 'Le code BIC/SWIFT doit contenir au moins 7 caractères.',

            'currency.required' => 'La devise est requise.',
            'currency.string' => 'La devise doit être une chaîne de caractères.',
            'currency.max' => 'La devise ne peut pas dépasser 4 caractères.',

            'country.required' => 'Le pays est requis.',
            'country.string' => 'Le pays doit être une chaîne de caractères.',
            'country.max' => 'Le nom du pays ne peut pas dépasser 255 caractères.',
        ];
    }
}
