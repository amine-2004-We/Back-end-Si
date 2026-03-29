<?php

namespace App\Http\Requests;

use App\Enums\CurrencyEnum;
use App\Enums\FinancialResourcesTypeEnum;
use App\Enums\FinancialStatusEnum;
use App\Enums\FinancialTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreFinancialResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_resources_type' => ['required', new Enum(FinancialResourcesTypeEnum::class)],
            'partner_id' => ['required', 'exists:partners,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'slice' => ['required', 'integer'],
            'amount_received' => ['required', 'numeric'],
            'slice_date' => ['required', 'date'],
            'currency' => ['required', new Enum(CurrencyEnum::class)],
            'financial_type' => ['required', new Enum(FinancialTypeEnum::class)],
            'signature_date' => ['required', 'date'],
            'project_start_date' => ['required', 'date'],
            'project_end_date' => ['nullable', 'date'],
            'financial_status' => ['required', new Enum(FinancialStatusEnum::class)],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'comments' => ['nullable', 'string'],
        ];
    }
}
