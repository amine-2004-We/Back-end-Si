<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyBankRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'exists:banks,id',
        ];
    }

    /**
     * Messages personnalisés pour les erreurs de validation.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'La liste des identifiants est requise.',
            'ids.array' => 'Le champ ids doit être un tableau.',
            'ids.*.exists' => 'Un ou plusieurs identifiants sélectionnés n’existent pas.',
        ];
    }
}
