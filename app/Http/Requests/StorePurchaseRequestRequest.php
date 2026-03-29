<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'department_id' => 'required|exists:departements,id',
            'project_id' => 'required|exists:projects,id',
            'observations' => 'nullable|string',
            'products' => 'array|required_without:packs',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'packs' => 'array|required_without:products',
            'packs.*.pack_id' => 'required|exists:packs,id',
            'priority'=>'required|in:Haute,Moyenne,Basse',
        ];
    }
}
