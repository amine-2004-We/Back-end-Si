<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles validation for deleting one or more Units.
 */
class DestroyUnitsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'unit_ids' => ['required', 'array'],
            'unit_ids.*' => ['integer', 'exists:units,id'], 
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unit_ids.required' => 'Au moins un ID d\'unité est requis pour la suppression.',
            'unit_ids.array' => 'Les IDs d\'unité doivent être fournis sous forme de tableau.',
            'unit_ids.*.integer' => 'Chaque ID d\'unité doit être un entier.',
            'unit_ids.*.exists' => 'Un ou plusieurs IDs d\'unité fournis n\'existent pas.',
        ];
    }
}