<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'issue_date'          => 'required|date',
            'debited_bank_account_id'     => 'required|integer|exists:project_bank_accounts,id', 
            'beneficiary_name'    => 'required|string|max:255',
            'beneficiary_bank_account_id' => 'required|integer|exists:project_bank_accounts,id', 
            'motif_type'          => 'required|string|in:facture,depense',
            'motif_id'            => 'required|integer',
            'amount'              => 'required|numeric|min:0.01',
            'status'              => 'required|in:en_attente,envoye,valide,rejete',
            'etbac_file_id'       => 'nullable|integer|exists:etbac_files,id', 
        ];
    }

    public function messages(): array
    {
        return [
            'motif_type.in' => 'Le type de motif doit être: facture ou depense.',
            'etbac_file_id.exists' => 'Le fichier ETBAC sélectionné n\'existe pas.',
        ];
    }
}