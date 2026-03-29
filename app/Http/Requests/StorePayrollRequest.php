<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StorePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // update with Gate/Policy if needed
    }


    public function rules(): array
    {
        return [
            'collaborator_id'           => [
                'required',
                'integer',
                'exists:collaborators,id',
                // --- THIS IS THE FIX ---
                // Ensures that the combination of collaborator and period is unique.
                Rule::unique('payrolls')->where(function ($query) {
                    return $query->where('period', $this->input('period'));
                })
            ],
            'period'                    => ['required', 'date'],
            'unjustified_absences_days' => ['nullable', 'integer', 'min:0'],
            'justified_absences_days'   => ['nullable', 'integer', 'min:0'],
            'maternity_days'            => ['nullable', 'integer', 'min:0'],
            'backpay_days'              => ['nullable', 'integer', 'min:0'],
            'contract_start_date'       => ['nullable', 'date'],
            'contract_end_date'         => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'exit_date'                 => ['nullable', 'date'],
            'deductions'                => ['nullable', 'array'],
            'deductions.*.label'        => ['required_with:deductions', 'string'],
            'deductions.*.amount'       => ['required_with:deductions', 'numeric', 'min:0'],
            'benefits'                  => ['nullable', 'array'],
            'benefits.*.label'          => ['required_with:benefits', 'string'],
            'benefits.*.amount'         => ['required_with:benefits', 'numeric', 'min:0'],
            'benefits_total'            => ['nullable', 'numeric', 'min:0'],
            'deductions_total'          => ['nullable', 'numeric', 'min:0'],
            'net_amount'                => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'collaborator_id.required' => 'Le collaborateur est obligatoire.',
            'collaborator_id.integer'  => 'L\'identifiant du collaborateur doit être un nombre entier.',
            'collaborator_id.exists'   => 'Le collaborateur sélectionné est introuvable.',
            // --- THIS IS THE FIX ---
            'collaborator_id.unique'   => 'Une fiche de paie existe déjà pour ce collaborateur pour cette période.',

            'period.required' => 'Le mois de paie est obligatoire.',
            'period.date'     => 'Le mois de paie doit être une date valide.',

            'unjustified_absences_days.integer' => 'Le nombre de jours d\'absence injustifiée doit être un entier.',
            'unjustified_absences_days.min'     => 'Le nombre de jours d\'absence injustifiée ne peut pas être négatif.',
            'justified_absences_days.integer'   => 'Le nombre de jours d\'absence justifiée doit être un entier.',
            'justified_absences_days.min'       => 'Le nombre de jours d\'absence justifiée ne peut pas être négatif.',
            'maternity_days.integer'            => 'Le nombre de jours de maternité doit être un entier.',
            'maternity_days.min'                => 'Le nombre de jours de maternité ne peut pas être négatif.',
            'backpay_days.integer'              => 'Le nombre de jours de rappel doit être un entier.',
            'backpay_days.min'                  => 'Le nombre de jours de rappel ne peut pas être négatif.',

            'contract_start_date.date'         => 'La date de début du contrat doit être une date valide.',
            'contract_end_date.date'           => 'La date de fin du contrat doit être une date valide.',
            'contract_end_date.after_or_equal' => 'La date de fin du contrat doit être égale ou postérieure à la date de début.',
            'exit_date.date'                   => 'La date de sortie doit être une date valide.',

            'deductions.array'                 => 'Les retenues doivent être une liste.',
            'deductions.*.label.required_with' => 'Le libellé de la retenue est obligatoire.',
            'deductions.*.amount.required_with'=> 'Le montant de la retenue est obligatoire.',
            'deductions.*.amount.numeric'      => 'Le montant de la retenue doit être un nombre.',
            'deductions.*.amount.min'          => 'Le montant de la retenue ne peut pas être négatif.',

            'benefits.array'                 => 'Les avantages doivent être une liste.',
            'benefits.*.label.required_with' => 'Le libellé de l\'avantage est obligatoire.',
            'benefits.*.amount.required_with'=> 'Le montant de l\'avantage est obligatoire.',
            'benefits.*.amount.numeric'      => 'Le montant de l\'avantage doit être un nombre.',
            'benefits.*.amount.min'          => 'Le montant de l\'avantage ne peut pas être négatif.',

            'benefits_total.numeric' => 'Le total des avantages doit être un nombre.',
            'benefits_total.min'     => 'Le total des avantages ne peut pas être négatif.',

            'deductions_total.numeric' => 'Le total des retenues doit être un nombre.',
            'deductions_total.min'     => 'Le total des retenues ne peut pas être négatif.',

            'net_amount.required' => 'Le montant net à payer est obligatoire.',
            'net_amount.numeric'  => 'Le montant net à payer doit être un nombre.',
            'net_amount.min'      => 'Le montant net à payer ne peut pas être négatif.',
        ];
    }
}

