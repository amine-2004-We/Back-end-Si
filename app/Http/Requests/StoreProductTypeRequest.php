<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductTypeRequest extends FormRequest
{
    /**
     * @return true
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('product_types', 'name')->whereNull('deleted_at'),
            ],
        ];
    }
}
