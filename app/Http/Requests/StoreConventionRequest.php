<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ConventionType;
use App\Enums\ConventionStatus;
use App\Enums\CurrencyEnum;
use App\Enums\ReportingPeriodicity;

class StoreConventionRequest extends FormRequest
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
        $rules = [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('conventions', 'title'),
            ],
            'partner_id' => [
            'required',
            'exists:partners,id',
            ],

             'project_id' => 'required|integer|exists:projects,id',

            'type' => [
                'required',
                'string',
                'in:' . implode(',', array_column(ConventionType::cases(), 'value')),
            ],
            'signed_at' => [
            'required',
            'date',
            ],
            'estimated_end_date' => [
            'required',
             'date'
            ],
            'status' => [
                'required',
                'string',
                'in:' . implode(',', array_column(ConventionStatus::cases(), 'value')),
            ],
            'signed_document' => 'required|file|mimes:pdf|max:10240',
            'amount' => 'nullable|numeric|min:0',
            'devise' => [
                'required',
                'in:' . implode(',', array_column(CurrencyEnum::cases(), 'value')),
            ],
            
            'amount_in_mad' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(fn () => $this->input('devise') && $this->input('devise') !== 'MAD'),
            ],

            'reporting_periodicity' => [
                'required',
                'string',
                'in:' . implode(',', array_column(ReportingPeriodicity::cases(), 'value')),
            ],
            'installments.*.installment_number' => ['required', 'integer', 'min:1'],

            'installments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'installments.*.due_date' => ['required', 'date', 'after_or_equal:signed_at'],
            'installments.*.trigger_condition' => ['required', 'string', 'max:500'],
            'installments.*.id' => ['nullable', 'integer', 'exists:financial_installments,id'],
            'installments.*.devise' => [
                'required',
                'in:' . implode(',', array_column(CurrencyEnum::cases(), 'value')),
            ],
            'tracked_individual_id' => ['required', 'exists:collaborators,id'],
            'observations' => ['nullable', 'string', 'max:500'],

             'duration_months' => ['required', 'integer', 'min:1'],
            'responsible_id' => ['required', 'integer', 'exists:collaborators,id'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            ];

            return $rules;
    }

    public function attributes(): array
    {
        return [
            'title' => 'Title',
            'type' => 'Type',
            'project_id' => 'Projet',
            'installments' => 'Tranches de financement',
            'installments.*.installment_number' => 'Numéro de tranche',
            'installments.*.amount' => 'Montant de la tranche',
            'installments.*.due_date' => 'Date prévue de versement',
            'installments.*.trigger_condition' => 'Condition de déclenchement',
            'responsible_id' => 'Responsable (collaborateur)',
            'tracked_individual_id' => 'Personne suivie (collaborateur)',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide ou n’existe pas.',
            'installments.min' => 'Une convention doit spécifier au moins une tranche de financement.',
            'installments.*.due_date.after_or_equal' => 'La date de versement doit être égale ou postérieure à la date de signature de la convention.',
            'installments.*.installment_number.required' => 'Le numéro de tranche est obligatoire pour chaque versement.',
           ];
    }
}