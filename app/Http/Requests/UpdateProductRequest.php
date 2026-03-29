<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class UpdateProductRequest
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * @return true
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('products', 'name')
                     ->ignore($this->route('product'))
                    ->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string',
           'category_id' => 'exists:categories,id',
            'product_type_id' => 'required|exists:product_types,id',
            'deleted_at' => 'nullable',
        ];
    }
}

