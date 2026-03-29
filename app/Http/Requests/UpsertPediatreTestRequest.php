<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertPediatreTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight'          => ['required', 'numeric', 'min:1', 'max:200'],
            'height'          => ['required', 'integer', 'min:10', 'max:250'],
            'refer_to_center' => ['required', 'boolean'],
            'observations'    => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'weight.required'       => 'Le poids de l’enfant est obligatoire.',
            'height.required'       => 'La taille de l’enfant est obligatoire.',
            'refer_to_center.required' => 'Veuillez préciser si l’enfant doit être référé.',
            'observations.required' => 'Veuillez saisir une observation.',
        ];
    }
}
