<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class StoreBudgetLineRequest
 */
class StoreBudgetLineRequest extends FormRequest
{
    /**
     * Autoriser l'exécution de cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255',Rule::unique('budget_lines', 'label')->whereNull('deleted_at')],
            'budget_category_id' => 'required|integer|exists:budget_categories,id',
        ];
    }


    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est requis.',
            'label.string' => 'Le libellé doit être une chaîne de caractères.',

            'project_id.required' => 'Le projet est requis.',
            'project_id.integer' => 'L\'identifiant du projet doit être un entier.',
            'project_id.exists' => 'Le projet sélectionné est invalide.',

            'label.required' => 'Le libellé est requis.',
            'label.string' => 'Le libellé doit être une chaîne de caractères.',
            'label.unique' => 'Le libellé doit être unique.',

            'budget_category_id.required' => 'La catégorie budgétaire est requise.',
            'budget_category_id.integer' => 'L\'identifiant de la catégorie doit être un entier.',
            'budget_category_id.exists' => 'La catégorie budgétaire sélectionnée est invalide.',

            'total_amount.required' => 'Le montant total est requis.',
            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'total_amount.min' => 'Le montant total ne peut pas être négatif.',

            'consumed_amount.required' => 'Le montant consommé est requis.',
            'consumed_amount.numeric' => 'Le montant consommé doit être un nombre.',
            'consumed_amount.min' => 'Le montant consommé ne peut pas être négatif.',

            'remaining_amount.required' => 'Le montant restant est requis.',
            'remaining_amount.numeric' => 'Le montant restant doit être un nombre.',
            'remaining_amount.min' => 'Le montant restant ne peut pas être négatif.',

            'status.required' => 'Le statut est requis.',
            'status.in' => 'Le statut doit être : active, consumed ou on_alert.',

            'partners.required' => 'La liste des partenaires est requise.',
            'partners.array' => 'Les partenaires doivent être fournis sous forme de tableau.',
            'partners.*.id.required' => 'L\'identifiant du partenaire est requis.',
            'partners.*.id.integer' => 'L\'identifiant du partenaire doit être un entier.',
            'partners.*.id.exists' => 'Le partenaire spécifié n\'existe pas.',
            'partners.*.allocated_amount.required' => 'Le montant alloué est requis pour chaque partenaire.',
            'partners.*.allocated_amount.numeric' => 'Le montant alloué doit être un nombre.',
            'partners.*.allocated_amount.min' => 'Le montant alloué ne peut pas être négatif.',
        ];
    }
}
