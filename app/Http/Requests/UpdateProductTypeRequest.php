<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class UpdateProductTypeRequest
 */
class UpdateProductTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255',
                Rule::unique('product_types', 'name')->ignore($this->route('productType'))->whereNull('deleted_at')
            ]
            ,
            'deleted_at' => 'nullable',
        ];
    }
}

