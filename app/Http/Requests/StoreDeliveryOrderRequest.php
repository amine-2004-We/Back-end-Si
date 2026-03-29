<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
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
           
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'quote_id' => ['nullable', 'integer', 'exists:quotes,id'],
            'delivery_request_id' => ['required', 'integer', 'exists:delivery_request,id'],
            'delivery_address' => ['nullable', 'string'],
            'expected_delivery_date' => ['required', 'date', 'before_or_equal:today'],
            'comments' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:Validée,En attente,Refusée'],
            'items' => ['required', 'array', 'min:1'],
           'items.*.article_id' => ['required', 'exists:articles,id'],
            'items.*.expected_quantity' => ['required', 'integer', 'min:1'],
        ];
    }


    public function messages()
    {
        return [
            'items.*.expected_quantity.min' => 'La quantité attendue doit être au moins de 1.',
            

        ];
    }
}
