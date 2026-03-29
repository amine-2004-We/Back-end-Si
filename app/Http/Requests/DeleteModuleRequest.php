<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteModuleRequest extends FormRequest
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
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:modules,id'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'La liste des identifiants est requise.',
            'ids.array'    => 'La liste des identifiants doit être un tableau.',
            'ids.*.integer'  => 'Chaque identifiant de module doit être un entier.',
            'ids.*.exists'   => 'Un ou plusieurs des modules sélectionnés sont introuvables.',
        ];
    }
}
