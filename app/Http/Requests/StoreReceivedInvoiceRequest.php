<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceivedInvoiceRequest extends FormRequest
{
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
            // The main payload must be an array of invoices with at least one item.
            'invoices'   => ['required', 'array', 'min:1'],

            // Validation rules for each object within the 'invoices' array.
            'invoices.*.unreceived_invoice_id' => [
                'required',
                'integer',
                Rule::exists('invoices', 'id')->where('status', Invoice::STATUS_UNRECEIVED)
            ],
            'invoices.*.supplier_invoice_number' => ['required', 'string', 'max:255'],
            'invoices.*.invoice_date'            => ['required', 'date', 'before_or_equal:today'],
            'invoices.*.reception_date'          => ['required', 'date', 'after_or_equal:invoices.*.invoice_date'],
            'invoices.*.due_date'                => ['required', 'date', 'after_or_equal:invoices.*.reception_date'],
            'invoices.*.accounting_account_id'   => ['required', 'integer', 'exists:third_party_accounts,id'],
            'invoices.*.subject'                 => ['sometimes', 'string', 'max:255'],
            'invoices.*.notes'                   => ['nullable', 'string'],
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
            'invoices.required' => 'La liste des factures est requise.',
            'invoices.min'      => 'Vous devez traiter au moins une facture.',

            'invoices.*.unreceived_invoice_id.exists' => 'La facture non parvenue sélectionnée est invalide ou a déjà été traitée.',
            'invoices.*.supplier_invoice_number.required' => 'Le numéro de facture du fournisseur est requis pour chaque facture.',
            'invoices.*.reception_date.after_or_equal' => 'La date de réception doit être égale ou postérieure à la date de facturation.',
            'invoices.*.due_date.after_or_equal' => 'La date d\'échéance doit être égale ou postérieure à la date de réception.',
            'invoices.*.accounting_account_id.required' => 'Le compte comptable est requis pour chaque facture.',
        ];
    }
}

