<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteInternalTrainerRequest extends FormRequest
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
            'ids.*' => ['integer', 'exists:internal_trainers,id'],
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
            'ids.array'    => 'Le champ ids doit être un tableau.',
            'ids.*.integer'  => 'Chaque identifiant doit être un entier.',
            'ids.*.exists'   => 'Un des formateurs internes sélectionnés est introuvable.',
        ];
    }
}
