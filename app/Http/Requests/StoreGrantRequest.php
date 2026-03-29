<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGrantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'exists:partners,id'],
            'convention_id' => ['required', 'exists:conventions,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'bank_account_id' => ['required', 'exists:project_bank_accounts,id'],
            'committed_amount' => ['required', 'numeric', 'min:0'],
            'received_amount' => ['required', 'numeric', 'min:0', 'lte:committed_amount'],
            'currency' => ['required', Rule::in(['MAD', 'EUR', 'USD'])],
            'agreement_date' => ['required', 'date'],
            'reception_method' => ['nullable', Rule::in(['Virement', 'Chèque', 'Cash'])],
            // 'status' => ['required', Rule::in(['En attente', 'Partiellement reçue', 'Reçue intégralement', 'Clôturée'])],
            'intended_use' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
           'proof_document_attachment_path' => ['file', 'nullable', 'mimes:jpg,jpeg,png,pdf,doc,docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:10240'],
'payment_schedule_attachment_path' => ['file', 'nullable', 'mimes:pdf,xls,xlsx', 'max:10240'],
            'received_dates' => ['nullable', 'array'],
            'received_dates.*' => ['date'],
        ];
    }
    protected function prepareForValidation()
    {
        if ($this->has('received_dates') && is_string($this->received_dates)) {
            $this->merge([
                'received_dates' => json_decode($this->received_dates, true)
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'La sélection d\'un compte bancaire est obligatoire.',
            'currency.in' => 'La devise sélectionnée est invalide.',
            'received_amount.lte' => 'Le montant reçu ne peut pas dépasser le montant engagé.',
            'status.in' => 'Le statut sélectionné est invalide.',
            'reception_method.in' => 'La méthode de réception sélectionnée est invalide.',
            'received_dates.*.date' => 'Chaque date reçue doit être une date valide.',
            'received_dates.array' => 'Les dates reçues doivent être fournies sous forme de tableau.',
            'committed_amount.min' => 'Le montant engagé doit être au moins de 0.',
            'received_amount.min' => 'Le montant reçu doit être au moins de 0.',
            'agreement_date.date' => 'La date de l\'accord doit être une date valide.',
            'partner_id.required' => 'La sélection d\'un partenaire est obligatoire.',
            'convention_id.required' => 'La sélection d\'une convention est obligatoire.',
            'project_id.required' => 'La sélection d\'un projet est obligatoire.',
            'partner_id.exists' => 'Le partenaire sélectionné est invalide.',
            'convention_id.exists' => 'La convention sélectionnée est invalide.',
            'project_id.exists' => 'Le projet sélectionné est invalide.',
            'bank_account_id.exists' => 'Le compte bancaire sélectionné est invalide.',
            'currency.required' => 'La sélection d\'une devise est obligatoire.',
            'status.required' => 'La sélection d\'un statut est obligatoire.',
            'committed_amount.required' => 'Le montant engagé est obligatoire.',
            'received_amount.required' => 'Le montant reçu est obligatoire.',
        ];
    }
}