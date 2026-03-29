<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'brand' => 'nullable|string|max:100',
            'unit' => 'required|in:mètre,unité,ramette,boîte,pièce,litre,bidon,session,jour,Autre',
            'reference_price' => 'numeric|min:0',
            'specifications' => 'nullable',
            'deleted_at' => 'nullable',
        ];
    }
}
