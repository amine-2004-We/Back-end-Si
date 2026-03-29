<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalltenderRequest extends FormRequest
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
            'supplier' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:255',
            'purchase_order_refs' => 'nullable|string|max:255',
            'calltender_type' => 'sometimes|string|max:50|in:Projet,Pluriannuel,Cadre,Autre',
            'start_date' => 'sometimes|date|before_or_equal:today',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'total_amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|max:10|in:MAD,EUR,USD',
            'conditions_path' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'responsible_id' => 'nullable|integer|exists:users,id',
            'status' => 'sometimes|string|max:50|in:En préparation,Signé,Résilié,Clôturé',
            'signature_date' => 'nullable|date|sometimes|required_if:status,Signé',
            'notes' => 'nullable|string|max:1000',

        ];
    }
}
