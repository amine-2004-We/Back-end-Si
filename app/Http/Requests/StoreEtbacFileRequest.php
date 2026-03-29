<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEtbacFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => 'required|integer|exists:project_bank_accounts,id',
            'beneficiary_id' => 'required|integer|exists:invoices,id',
            'status' => 'required|in:prepared,sent,rejected,executed',
            'transfer_order_ids' => 'sometimes|array',
            'transfer_order_ids.*' => 'integer|exists:transfer_orders,id',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Le compte bancaire émetteur est obligatoire.',
            'bank_account_id.exists' => 'Le compte bancaire sélectionné n\'existe pas.',
            'beneficiary_id.required' => 'La facture bénéficiaire est obligatoire.',
            'beneficiary_id.exists' => 'La facture sélectionnée n\'existe pas.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être: préparé, envoyé, rejeté ou exécuté.',
        ];
    }
}