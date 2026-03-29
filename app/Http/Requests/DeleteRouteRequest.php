<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRouteRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:routes,id'],
        ];
    }

    /**
     * Get the custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'La liste des identifiants est requise.',
            'ids.array'    => 'La liste des identifiants doit être un tableau.',
            'ids.*.integer'  => 'Chaque identifiant dans la liste doit être un entier.',
            'ids.*.exists'   => 'Un ou plusieurs trajets sélectionnés sont introuvables.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ids'   => 'liste d\'identifiants',
            'ids.*' => 'identifiant de trajet',
        ];
    }
}
