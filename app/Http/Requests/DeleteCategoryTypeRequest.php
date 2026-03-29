<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCategoryTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => [
                'required',
                'array',
                'min:1',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:line_types,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Les IDs sont requis.',
            'ids.array' => 'Les IDs doivent être un tableau.',
            'ids.min' => 'Au moins un ID est requis.',
            'ids.*.required' => 'L\'ID est requis.',
            'ids.*.integer' => 'L\'ID doit être un entier.',
            'ids.*.exists' => 'L\'ID n\'existe pas.',
        ];
    }
}
