<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ChequeStatusEnum;

class UpdateChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'sometimes|required|string|max:255',
            'emission_date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|gt:0',
            'beneficiary_id' => 'sometimes|required|integer|exists:beneficiaires,id',
            // 'bank_id' => 'sometimes|required|integer|exists:banks,id',
            'project_bank_account_id' => 'sometimes|required|integer|exists:project_bank_accounts,id',
            'status' => ['sometimes', 'required', Rule::in(array_column(ChequeStatusEnum::cases(), 'value'))],
        ];
    }
}