<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadPurchaseListRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'purchase_list_id' => [
                'required',
                'integer',
                'exists:purchase_lists,id',
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'purchase_list_id.required' => 'La liste d\'achat est requise',
            'purchase_list_id.integer' => 'L\'ID de la liste d\'achat doit être un nombre',
            'purchase_list_id.exists' => 'La liste d\'achat sélectionnée n\'existe pas',
        ];
    }
}
