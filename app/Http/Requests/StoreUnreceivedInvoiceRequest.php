<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnreceivedInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('invoices')->whereNull('deleted_at')],
            'subject'        => ['nullable', 'string', 'max:255'],
            'delivery_receipt_id'=>['required', 'integer', 'exists:delivery_receipts,id'],
            'notes'          => ['nullable', 'string'],
            'due_date' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_number.required' => 'Le numéro de la facture (interne) est requis.',
            'invoice_number.unique'   => 'Ce numéro de facture est déjà utilisé.',
        ];
    }
}
