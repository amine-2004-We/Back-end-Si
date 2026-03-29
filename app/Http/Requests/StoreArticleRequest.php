<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // à adapter si besoin d’une autorisation
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('articles', 'name')->whereNull('deleted_at'),
            ],
            'product_id' => 'required|exists:products,id',
            'brand' => 'nullable|string|max:100',
            'unit' => 'required|in:mètre,unité,ramette,boîte,pièce,litre,bidon,session,jour,Autre',
            'reference_price' => 'numeric|min:0',
            'specifications' => 'nullable',
        ];
    }
      public function messages(): array
    {
        return [
            'name.required'       => "Le nom de l'article est obligatoire.",
            'name.unique'         => "Ce nom d'article est déjà utilisé.",
            'product_id.required' => 'Le produit est obligatoire.',
            'product_id.exists'   => "Le produit sélectionné n'est pas valide.",
            
        ];
    }
}
