<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation de la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $status = $this->input('status');

        return [
            'receipt_id' => [
                'required',
                'integer',
            ],

            'invoice_number' => [
                Rule::requiredIf($status !== 'Non parvenue'),
                'nullable',
                'string',
                'max:255',
                Rule::unique('invoices')->whereNull('deleted_at'),
            ],

            'invoice_date' => [
                Rule::requiredIf($status !== 'Non parvenue'),
                'nullable',
                'date',
            ],

            'due_date' => ['nullable', 'date'],

            'status' => [
                'nullable',
                Rule::in([
                    'Non parvenue',
                    'En attente de validation',
                    'Annulé',
                    'En cours de traitement',
                    'Payé',
                    'Rejeté',
                    'Comptabilisé',
                    'Traité',
                    'Validé (Trésorerie)',
                    'Validé 1 (Comptabilité)',
                    'Validé 2 (CG)',
                ]),
            ],

            'subject' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Messages personnalisés en cas d'erreur de validation.
     */
    public function messages(): array
    {
        return [
            'receipt_id.required' => 'La référence du reçu est obligatoire.',
            'receipt_id.integer' => 'La référence du reçu doit être un entier.',
            'invoice_number.required' => 'Le numéro de facture est obligatoire sauf pour une facture non parvenue.',
            'invoice_number.unique' => 'Le numéro de facture est déjà utilisé.',
            'invoice_date.required' => 'La date de facture est obligatoire sauf pour une facture non parvenue.',
            'invoice_date.date' => 'La date de facture doit être une date valide.',
            'due_date.date' => 'La date d’échéance doit être une date valide.',
            'subject.required' => 'Le sujet est obligatoire.',
            'status.in' => 'Le statut sélectionné est invalide.',
        ];
    }
}
