<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialInstallmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed (policies/gates can be used)
        return true;
    }

    public function rules(): array
    {
        return [
            'convention_id' => ['required', 'integer', 'exists:conventions,id'],
            'installment_number' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'reception_mode' => ['nullable', 'string', 'max:100'],
            'reception_date' => ['nullable', 'date'],
            'is_ttc' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'trigger_condition' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'proof_document' => ['nullable'],
            'devise' => ['nullable', 'string', 'max:3'],
            'receptions' => ['nullable', 'array'],
            'receptions.*.reception_date' => ['nullable', 'date'],
            'receptions.*.amount_received' => ['nullable', 'numeric', 'min:0'],
            'receptions.*.reception_mode' => ['nullable', 'string', 'max:100'],
        ];
    }
}
