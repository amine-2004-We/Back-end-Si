<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'request_ids' => 'sometimes|array|min:1',
            'articles' => 'sometimes|array|min:1',
            'articles.*.article_id' => 'required_with:articles|integer|exists:articles,id',
            'articles.*.quantity' => 'required_with:articles|integer|min:1',
            'total_quantity' => 'sometimes|integer|min:1',
            'priority' => 'nullable|in:High,Medium,Low',
            'emitting_department' => 'sometimes|integer|exists:departements,id',
            'observations' => 'nullable|string',
        ];
    }


    public function messages(): array
    {
        return [
            'request_ids.array' => 'Le champ des identifiants de demande doit être un tableau.',
            'request_ids.min' => 'Vous devez sélectionner au moins une demande.',

            'articles.array' => 'Les articles doivent être fournis dans un tableau.',
            'articles.min' => 'Vous devez ajouter au moins un article.',

            'articles.*.article_id.required_with' => "L'identifiant de l'article est requis.",
            'articles.*.article_id.integer' => "L'identifiant de l'article doit être un nombre entier.",
            'articles.*.article_id.exists' => "Un des articles sélectionnés est invalide ou n'existe pas.",

            'articles.*.quantity.required_with' => 'La quantité pour chaque article est obligatoire.',
            'articles.*.quantity.integer' => 'La quantité pour chaque article doit être un nombre entier.',
            'articles.*.quantity.min' => 'La quantité pour chaque article doit être au moins de 1.',

            'total_quantity.integer' => 'La quantité totale doit être un nombre entier.',
            'total_quantity.min' => 'La quantité totale doit être au moins de 1.',

            'priority.in' => 'La priorité sélectionnée est invalide. Les valeurs acceptées sont : haute, moyenne, basse.',

            'observations.string' => 'Le champ observations doit être une chaîne de caractères.',
        ];
    }
}
