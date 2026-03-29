<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEtbacFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => 'sometimes|integer|exists:project_bank_accounts,id',
            'beneficiary_id' => 'sometimes|integer|exists:invoices,id',
            'status' => 'sometimes|in:prepared,sent,rejected,executed',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_account_id.exists' => 'Le compte bancaire sélectionné n\'existe pas.',
            'beneficiary_id.exists' => 'La facture sélectionnée n\'existe pas.',
            'status.in' => 'Le statut doit être: préparé, envoyé, rejeté ou exécuté.',
        ];
    }
}