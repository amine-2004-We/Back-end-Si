<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ServiceProvisionTypeEnum;

class StoreServiceProvisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_line_id' => 'required|integer|exists:budget_lines,id',
            'provision_date' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'supplier' => 'required|string|max:255',
            'description' => 'required|string',
            'justification' => 'required|file|mimes:pdf,jpg,png|max:5120', // 5MB Max
            'type' => ['required', Rule::in(array_column(ServiceProvisionTypeEnum::cases(), 'value'))],
        ];
    }
}
