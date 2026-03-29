<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ServiceProvisionTypeEnum;

class UpdateServiceProvisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_line_id' => 'sometimes|required|integer|exists:budget_lines,id',
            'provision_date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|gt:0',
            'supplier' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'justification' => 'sometimes|nullable|file|mimes:pdf,jpg,png|max:5120', // 5MB Max
            'type' => ['sometimes', 'required', Rule::in(array_column(ServiceProvisionTypeEnum::cases(), 'value'))],
        ];
    }
}