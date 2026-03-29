<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // autoriser la requête
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('banks', 'name')->whereNull('deleted_at')],
            'bank_code' => ['required', 'string', 'max:255', Rule::unique('banks', 'bank_code')->whereNull('deleted_at')],
            'bic_swift' => 'nullable|string|max:11|min:7',
            'currency' => 'required|string|max:4',
            'country' => 'required|string|max:255'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la banque est requis.',
            'name.string' => 'Le nom de la banque doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la banque ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce nom de banque est déjà utilisé.',

            'bank_code.required' => 'Le code de la banque est requis.',
            'bank_code.string' => 'Le code de la banque doit être une chaîne de caractères.',
            'bank_code.max' => 'Le code de la banque ne doit pas dépasser 255 caractères.',
            'bank_code.unique' => 'Ce code de banque est déjà utilisé.',

            'bic_swift.string' => 'Le code BIC/SWIFT doit être une chaîne de caractères.',
            'bic_swift.max' => 'Le code BIC/SWIFT ne doit pas dépasser 11 caractères.',
            'bic_swift.min' => 'Le code BIC/SWIFT doit contenir au moins 7 caractères.',

            'currency.required' => 'La devise est requise.',
            'currency.string' => 'La devise doit être une chaîne de caractères.',
            'currency.max' => 'La devise ne doit pas dépasser 4 caractères.',

            'country.required' => 'Le pays est requis.',
            'country.string' => 'Le pays doit être une chaîne de caractères.',
            'country.max' => 'Le pays ne doit pas dépasser 255 caractères.',
        ];
    }
}
