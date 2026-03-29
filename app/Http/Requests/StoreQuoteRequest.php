<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_list_id' => ['required', 'integer', 'exists:purchase_lists,id'],
            'supplier_id'         => ['required', 'integer', 'exists:suppliers,id'],

            'quote_date'   => ['required', 'date', 'before_or_equal:today'],
            'valid_until'  => ['nullable', 'date', 'after_or_equal:quote_date'],

            'subject'      => ['required', 'string'],

            'payment_terms' => ['nullable', Rule::in(['upon_receipt', '30d_end_month', 'other'])],

            'remarks'       => ['nullable', 'string'],
            'attachment_path' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],

            'estimated_delivery_days' => 'nullable|numeric',
            'total_amount_ht'  => ['nullable', 'numeric', 'min:0'],
            'vat_amount'       => ['nullable', 'numeric', 'min:0'],
            'total_amount_ttc' => ['nullable', 'numeric', 'min:0'],

            'items'                    => ['required', 'array', 'min:1'],
            'items.*.article_id'       => ['required', 'integer', 'exists:articles,id'],
            'items.*.quantity'         => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price_ht'    => ['required', 'numeric', 'min:0'],
            'items.*.tva_rate'         => ['nullable', 'numeric', 'between:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'purchase_request_id.required' => 'La demande d\'achat est requise.',
            'supplier_id.required'         => 'Le fournisseur est requis.',
            'quote_date.required'          => 'La date du devis est requise.',
            'subject.required'             => 'L\'objet est requis.',
            'items.required'               => 'Au moins un article doit être fourni.',
            'items.*.article_id.exists'    => 'Un des articles n\'existe pas.',
            'attachment_path' => 'L\'objet est requis (pdf,jpg,jpeg,png max = 10M).'
        ];
    }
}
