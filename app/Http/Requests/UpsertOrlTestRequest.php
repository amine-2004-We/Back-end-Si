<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertOrlTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ear_pain_regularly' => 'required|boolean',
            'hearing_problem'    => 'required|boolean',
            'refer_to_center'    => 'required|boolean',
            'observations'       => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'ear_pain_regularly.required' => 'Veuillez préciser si l’enfant se plaint régulièrement d’avoir mal aux oreilles',
            'hearing_problem.required'    => 'Veuillez préciser si vous pensez que l’enfant n’entend pas suffisamment bien',
            'refer_to_center.required'    => 'Veuillez préciser si l’enfant doit être référé.',
            'observations.required'       => 'Veuillez saisir une observation.',
        ];
    }
}
