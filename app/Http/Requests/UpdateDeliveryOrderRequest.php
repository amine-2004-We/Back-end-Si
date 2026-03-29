<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryOrderRequest extends FormRequest
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

            'supplier_id' => ['sometimes', 'integer', 'exists:suppliers,id'],
            'purchase_order_id' => ['sometimes', 'integer', 'exists:purchase_orders,id'],
            'quote_id' => ['nullable', 'integer', 'exists:quotes,id'],
            'delivery_request_id' => ['required', 'integer', 'exists:delivery_request,id'],
            'delivery_address' => ['nullable', 'string'],
            'expected_delivery_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'comments' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:Préparé,Émis,Livré,Annulé'],
            'items' => ['sometimes', 'array', 'min:1'],
           'items.*.article_id' => ['required_with:items', 'exists:articles,id'],
            'items.*.expected_quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
