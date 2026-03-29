<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // N'oubliez pas d'importer la classe Rule

class StorePackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('packs', 'name')->whereNull('deleted_at')
            ],
            'description' => [
                'nullable', // facultatif
                'string'],
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du pack est obligatoire.',
            'name.unique'   => 'Ce nom de pack est déjà utilisé.',

            'products.required' => 'Vous devez ajouter au moins un produit.',
            'products.min'      => 'Vous devez ajouter au moins un produit.',


            'products.*.product_id.required' => "Veuillez sélectionner un produit pour chaque ligne.",
            'products.*.product_id.exists'   => "Un des product sélectionnés n'est pas valide.",

            'products.*.quantity.required' => 'La quantité est obligatoire pour chaque produit.',
            'products.*.quantity.min'      => 'La quantité pour chaque produit doit être au moins de 1.',
        ];
    }
}
