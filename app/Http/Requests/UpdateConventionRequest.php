<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ConventionType;
use App\Enums\ConventionStatus;
use App\Enums\CurrencyEnum;
use App\Enums\ReportingPeriodicity;

class UpdateConventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $installmentsData = $this->input('installments');

        if (is_string($installmentsData)) {
            $this->merge([
                'installments' => json_decode($installmentsData, true) ?? [],
            ]);
        }
    }

    public function rules()
    {
        $conventionId = $this->route('convention');

        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
                // Rule::unique('conventions', 'title')->ignore($conventionId),
            ],
            'partner_id' => [
                'sometimes',
                'exists:partners,id',
            ],
            'project_id' => 'sometimes|integer|exists:projects,id',
            'type' => [
                'sometimes',
                'string',
                'in:' . implode(',', array_column(ConventionType::cases(), 'value')),
            ],
            'signed_at' => [
                'sometimes',
                'date',
            ],
            'estimated_end_date' => [
                'sometimes',
                'date'
            ],
            'status' => [
                'sometimes',
                'string',
                'in:' . implode(',', array_column(ConventionStatus::cases(), 'value')),
            ],
            'signed_document' => 'nullable|file|mimes:pdf|max:10240', // Changed to nullable for updates
            'amount' => 'nullable|numeric|min:0',
            'amount_in_mad' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(fn () => $this->input('devise') && $this->input('devise') !== 'MAD'),
            ],
            'devise' => [
                'sometimes',
                'in:' . implode(',', array_column(CurrencyEnum::cases(), 'value')),
            ],
            'reporting_periodicity' => [
                'sometimes',
                'string',
                'in:' . implode(',', array_column(ReportingPeriodicity::cases(), 'value')),
            ],
            'installments' => 'nullable|array', // Allow updating without installments
            'installments.*.installment_number' => ['required', 'integer', 'min:1'],
            'installments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'installments.*.due_date' => ['required', 'date', 'after_or_equal:signed_at'],
            'installments.*.trigger_condition' => ['required', 'string', 'max:500'],
            'installments.*.id' => ['nullable', 'integer', 'exists:financial_installments,id'],
            'installments.*.devise' => [
                'required',
                'in:' . implode(',', array_column(CurrencyEnum::cases(), 'value')),
            ],
            'tracked_individual_id' => ['sometimes', 'exists:collaborators,id'],
            'observations' => ['nullable', 'string', 'max:500'],
        ];
    }
}