<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'request_ids' => 'required|array|min:1',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|integer|exists:articles,id',
            'articles.*.quantity' => 'required|integer|min:1',
            'total_quantity' => 'required|integer|min:1',
            'priority' => 'nullable|in:high,medium,low',
            'emitting_department' => 'required|integer|exists:departements,id',
            'observations' => 'nullable|string',
            
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
            'request_ids.required' => 'Le champ des identifiants de demande est obligatoire.',
            'request_ids.array' => 'Le champ des identifiants de demande doit être un tableau.',
            'request_ids.min' => 'Vous devez sélectionner au moins une demande.',

            'articles.required' => 'La liste des articles est obligatoire.',
            'articles.array' => 'Les articles doivent être fournis dans un tableau.',
            'articles.min' => 'Vous devez ajouter au moins un article.',

            'articles.*.article_id.required' => "L'identifiant de l'article est requis.",
            'articles.*.article_id.integer' => "L'identifiant de l'article doit être un nombre entier.",
            'articles.*.article_id.exists' => "Un des articles sélectionnés est invalide ou n'existe pas.",

            'articles.*.quantity.required' => 'La quantité pour chaque article est obligatoire.',
            'articles.*.quantity.integer' => 'La quantité pour chaque article doit être un nombre entier.',
            'articles.*.quantity.min' => 'La quantité pour chaque article doit être au moins de 1.',

            'total_quantity.required' => 'La quantité totale est obligatoire.',
            'total_quantity.integer' => 'La quantité totale doit être un nombre entier.',
            'total_quantity.min' => 'La quantité totale doit être au moins de 1.',

            'priority.in' => 'La priorité sélectionnée est invalide. Les valeurs acceptées sont : haute, moyenne, basse.',

            'observations.string' => 'Le champ observations doit être une chaîne de caractères.',

        ];
    }
}
