<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:packs,name,' . $this->route('pack')->id,
            'description' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'deleted_at' => 'nullable',
        ];
    }
}
