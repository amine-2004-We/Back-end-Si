<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportGeographicDataRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240'], // Now accepts xlsx and xls
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
            'file.required' => 'Un fichier est requis pour l\'importation.',
            'file.file' => 'Le fichier fourni n\'est pas valide.',
            'file.mimes' => 'Le fichier doit être au format CSV, Excel (XLSX, XLS).', // Updated message
            'file.max' => 'La taille du fichier ne doit pas dépasser 10 Mo.',
        ];
    }
}