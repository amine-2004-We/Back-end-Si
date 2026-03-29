<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetLineProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'budget_line_id' => ['required', 'exists:budget_lines,id',Rule::unique('budget_line_project', 'budget_line_id')->where('project_id', $this->project_id)],
            'total_amount' => 'nullable|numeric|min:0',
            'consumed_amount' => 'nullable|numeric|min:0',
            'reliquate_amount' => 'nullable|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'unit_amount' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'partners' => 'nullable|array',
            'partners.*.partner_id' => 'required|exists:partners,id',
            'partners.*.allocated_amount' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'project_id.required' => 'Le projet est obligatoire.',
            'project_id.exists'   => 'Le projet sélectionné est invalide.',

            'budget_line_id.required' => 'La ligne budgétaire est obligatoire.',
            'budget_line_id.exists'   => 'La ligne budgétaire sélectionnée est invalide.',
            'budget_line_id.unique'   => 'Cette ligne budgétaire existe déjà dans ce projet.',

            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'total_amount.min'     => 'Le montant total doit être supérieur ou égal à 0.',

            'consumed_amount.numeric' => 'Le montant consommé doit être un nombre.',
            'consumed_amount.min'     => 'Le montant consommé doit être supérieur ou égal à 0.',

            'reliquate_amount.numeric' => 'Le reliquat doit être un nombre.',
            'reliquate_amount.min'     => 'Le reliquat doit être supérieur ou égal à 0.',

            'remaining_amount.numeric' => 'Le montant restant doit être un nombre.',
            'remaining_amount.min'     => 'Le montant restant doit être supérieur ou égal à 0.',

            'unit_amount.numeric' => 'Le montant unitaire doit être un nombre.',
            'unit_amount.min'     => 'Le montant unitaire doit être supérieur ou égal à 0.',

            'quantity.numeric' => 'La quantité doit être un nombre.',
            'quantity.min'     => 'La quantité doit être supérieure ou égale à 0.',
        ];
    }

}
