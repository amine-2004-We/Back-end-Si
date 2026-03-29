<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Make sure Rule is imported

class DestroyAbsenceRequest extends FormRequest
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
            'ids' => 'required|array',
            'ids.*' => ['required', 'integer', Rule::exists('absences', 'id')->whereNull('deleted_at')],
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
            'ids.required' => 'La liste des IDs est requise.',
            'ids.array' => 'Les IDs doivent être fournis sous forme de tableau.',
            'ids.*.required' => 'Chaque ID doit être fourni.',
            'ids.*.integer' => 'Chaque ID doit être un entier.',
            'ids.*.exists' => 'Un ou plusieurs des IDs d\'absence fournis n\'existent pas.',
        ];
    }
}
