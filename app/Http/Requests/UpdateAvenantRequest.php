<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvenantRequest extends FormRequest
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
            'marche_id' => 'sometimes|integer|exists:calltenders,id',
            'subject' => 'sometimes|string|max:1000',
            'modification_nature' => 'sometimes|string|in:Financier,Périmètre,Durée,Autre',
            'additional_amount' => 'nullable|numeric|min:0',
            'new_end_date' => 'nullable|date|after_or_equal:today',
            'document_path' => 'nullable|file|mimes:pdf|max:2048',
            'responsible_id' => 'nullable|integer|exists:collaborators,id',
            'status' => 'sometimes|string|in:En préparation,Signé,Annulé',
            'signature_date' => 'sometimes|date',
            'observations' => 'nullable|string|max:1000',
        ];
    }
}

