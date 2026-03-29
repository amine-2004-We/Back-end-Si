<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyBudgetLineRequest extends FormRequest
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
            'ids' => 'required|array',
            'ids.*' => 'exists:budget_lines,id',
        ];
    }

    /**
     * Messages de validation personnalisés en français.
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'La liste des identifiants est requise.',
            'ids.array' => 'La liste des identifiants doit être un tableau.',
            'ids.*.exists' => 'L’un des identifiants spécifiés n’existe pas dans les lignes budgétaires.',
        ];
    }
}
