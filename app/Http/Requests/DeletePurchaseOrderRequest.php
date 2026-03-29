<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeletePurchaseOrderRequest extends FormRequest
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
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'exists:purchase_orders,id'],
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => 'Aucune commande choisie.',
            'ids.*.required' => 'Veuillez choisir au moins une commande.',
            'ids.*.exists' => 'La commande choisie n\'existe pas.',
        ];
    }
}
