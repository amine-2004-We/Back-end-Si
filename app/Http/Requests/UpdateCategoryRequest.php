<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class UpdateCategoryRequest
 */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * @return true
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('categories', 'name')
                    ->ignore($this->route('category'), 'id')->whereNull('deleted_at'), // ou 'category' selon ta route
            ],
            'description' => 'nullable|string',
            'deleted_at' => 'nullable',
        ];
    }
}

