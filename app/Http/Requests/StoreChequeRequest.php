<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ChequeStatusEnum;

class StoreChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255',
            'emission_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'beneficiary_id' => 'required|integer|exists:beneficiaires,id',
            // 'bank_id' => 'required|integer|exists:banks,id',
            'project_bank_account_id' => 'required|integer|exists:project_bank_accounts,id',
            'status' => ['required', Rule::in(array_column(ChequeStatusEnum::cases(), 'value'))],
        ];
    }
}