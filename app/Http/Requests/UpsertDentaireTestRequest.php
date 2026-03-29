<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertDentaireTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'has_six_year_molar' => 'required|boolean',
            'six_year_molar_cariee' => 'required|boolean',
            'refer_to_center' => 'required|boolean',
            'observations' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'has_six_year_molar.required' => 'Veuillez préciser si l’enfant a la dent de 6 ans',
            'six_year_molar_cariee.required' => 'Veuillez préciser si la dent de 6 ans est cariée',
            'refer_to_center.required' => 'Veuillez préciser si l’enfant doit être référé.',
            'observations.required' => 'Veuillez saisir une observation.',
        ];
    }
}
