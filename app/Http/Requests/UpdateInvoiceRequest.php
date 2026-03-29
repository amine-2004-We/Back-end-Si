<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends FormRequest
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
            'supplier_invoice_number' => ['sometimes', 'string', 'max:255'],
            'invoice_date'            => ['sometimes', 'date', 'before_or_equal:today'],
            'reception_date'          => ['sometimes', 'date', 'after_or_equal:invoice_date'],
            'due_date'                => ['sometimes', 'date', 'after_or_equal:reception_date'],
            'accounting_account_id'   => ['sometimes', 'integer', 'exists:third_party_accounts,id'],

            // General fields
            'subject'                 => ['sometimes', 'string', 'max:255'],
            'notes'                   => ['nullable', 'string'],
            'status'                  => ['sometimes', Rule::in(Invoice::STATUSES)],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reception_date.after_or_equal' => 'La date de réception doit être égale ou postérieure à la date de facturation.',
            'due_date.after_or_equal'       => 'La date d\'échéance doit être égale ou postérieure à la date de réception.',
            'accounting_account_id.exists'  => 'Le compte comptable sélectionné est introuvable.',
            'invoice_number.prohibited'     => 'Le numéro de facture interne ne peut pas être modifié.',
        ];
    }
}

