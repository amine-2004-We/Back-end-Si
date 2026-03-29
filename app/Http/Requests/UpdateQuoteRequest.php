<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_list_id' => ['sometimes', 'integer', 'exists:purchase_lists,id'],
            'supplier_id'         => ['sometimes', 'integer', 'exists:suppliers,id'],

            'quote_date'   => ['sometimes', 'date', 'before_or_equal:today'],
            'valid_until'  => ['sometimes', 'date', 'after_or_equal:quote_date'],

            'subject'      => ['sometimes', 'string'],

            'payment_terms' => ['sometimes', 'nullable', Rule::in(['upon_receipt', '30d_end_month', 'other'])],
            'estimated_delivery_days' => 'nullable|numeric',
            'remarks'       => ['sometimes', 'string'],
            'total_amount_ht'  => ['sometimes', 'numeric', 'min:0'],
            'vat_amount'       => ['sometimes', 'numeric', 'min:0'],
            'total_amount_ttc' => ['sometimes', 'numeric', 'min:0'],
            'vat_rate'         => ['sometimes', 'numeric', 'between:0,1'],

            'items'                 => ['sometimes', 'array', 'min:1'],
            'items.*.article_id'    => ['sometimes', 'integer', 'exists:articles,id'],
            'items.*.quantity'      => ['sometimes', 'numeric', 'min:0.01'],
            'items.*.unit_price_ht' => ['sometimes', 'numeric', 'min:0'],
            'items.*.tva_rate' => ['nullable', 'numeric', 'between:0,1'],
            'attachment_path' => ['sometimes', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.article_id.exists' => 'Un des articles n\'existe pas.',
            'items.required_with'       => 'Tous les champs des articles sont requis.',
            'attachment_path.file'      => 'Le fichier doit être un document valide (pdf, jpg, jpeg, png).',
            'attachment_path.max'       => 'Le fichier ne peut pas dépasser 10 Mo.',
            'purchase_request_id.required' => 'La demande d\'achat est requise.',
            'supplier_id.required'         => 'Le fournisseur est requis.',
            'quote_date.required'          => 'La date du devis est requise.',
            'subject.required'             => 'L\'objet est requis.',
            'items.required'               => 'Au moins un article doit être fourni.',
        ];
    }
}
