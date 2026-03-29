<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' =>  [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.unique' => 'Ce nom de produit est déjà utilisé.',
            'category_id.required' => 'La catégorie du produit est obligatoire.',
            'category_id.exists' => "La catégorie sélectionnée n'est pas valide.",
            'product_type_id.required' => 'Le type de produit est obligatoire.',
            'product_type_id.exists' => "Le type de produit sélectionné n'est pas valide.",
        ];
    }
}

