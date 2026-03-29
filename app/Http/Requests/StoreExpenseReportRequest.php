<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseReportRequest extends FormRequest
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
            'mission_order_id' => 'nullable|exists:mission_orders,id',
            'status' => 'sometimes|in:created,submitted,validated_manager,validated_treasury,validated_accounting,rejected,paid',
            'total_amount' => 'required|numeric|min:0',

            'expense_lines' => 'sometimes|array',
            'expense_lines.*.designation' => 'required_with:expense_lines|string|max:255',
            'expense_lines.*.type' => 'required_with:expense_lines|in:restauration,deplacement,hebergement,transport,autre',
            'expense_lines.*.date' => 'required_with:expense_lines|date',
            'expense_lines.*.amount' => 'required_with:expense_lines|numeric|min:0.01',
            'budget_line_id' => 'required|exists:budget_lines,id',
            'expense_lines.*.manager_amount' => 'nullable|numeric|min:0',
            'expense_lines.*.finance_amount' => 'nullable|numeric|min:0',

            'expense_lines.*.label' => 'nullable|string|max:255',
            'expense_lines.*.departure_id' => 'nullable|required_if:expense_lines.*.type,deplacement|exists:provinces,id',
            'expense_lines.*.arrival_id' => 'nullable|required_if:expense_lines.*.type,deplacement|exists:provinces,id',
            'expense_lines.*.transport_mode' => 'nullable|required_if:expense_lines.*.type,deplacement|in:train,taxi,avion,voiture,bus',
            'expense_lines.*.justification' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Le projet est obligatoire',
            'project_id.exists' => 'Le projet sélectionné n\'existe pas',

            'budget_line_id.required' => 'La ligne budgétaire est obligatoire',
            'budget_line_id.exists' => 'La ligne budgétaire sélectionnée n\'existe pas',

            'expense_lines.*.departure_id.required_if' => 'Le lieu de départ est requis pour les déplacements',
            'expense_lines.*.arrival_id.required_if' => 'Le lieu d\'arrivée est requis pour les déplacements',
            'expense_lines.*.transport_mode.required_if' => 'Le mode de transport est requis pour les déplacements',

            'expense_lines.*.justification.file' => 'Le justificatif doit être un fichier',
            'expense_lines.*.justification.mimes' => 'Le justificatif doit être un PDF, JPG ou PNG',
            'expense_lines.*.justification.max' => 'Le justificatif ne doit pas dépasser 5MB',
        ];
    }
}
