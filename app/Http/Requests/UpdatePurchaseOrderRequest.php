<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quote_id'     => ['sometimes', 'integer', 'exists:quotes,id'],
            'supplier_id'  => ['sometimes', 'integer', 'exists:suppliers,id'],
            'issuer_id'    => ['sometimes', 'integer', 'exists:users,id'],
            'purchase_request_id' => ['sometimes', 'integer', 'exists:purchase_requests,id'],

            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.article_id'     => ['required', 'integer'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price_ht'  => ['required', 'numeric', 'min:0'],
            'items.*.tva_rate'       => ['required', 'numeric', 'min:0'],

            'subject'      => ['sometimes', 'string'],
            'issue_date'   => ['sometimes', 'date', 'before_or_equal:today'],

            'total_amount_ttc' => ['sometimes', 'numeric', 'min:0'],
            'currency'         => ['sometimes', Rule::in(['MAD', 'EUR', 'USD'])],
            'payment_method'   => ['sometimes', Rule::in(['transfer', 'check', 'cash'])],

            'delivery_lead_time_days' => ['nullable', 'integer', 'min:0'],
            'terms_file_path'         => ['nullable', 'string'],

            'status' => ['sometimes', Rule::in(['draft', 'in_review', 'approved', 'cancelled'])],

            'validated_by_procurement_manager' => ['sometimes', 'boolean'],
            'validated_by_controlling'         => ['sometimes', 'boolean'],
            'validated_by_board'               => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'quote_id.integer'  => 'L’identifiant du devis doit être un entier.',
            'quote_id.exists'   => 'Le devis sélectionné est introuvable.',

            'supplier_id.integer'  => 'L’identifiant du fournisseur doit être un entier.',
            'supplier_id.exists'   => 'Le fournisseur sélectionné est introuvable.',

            'issuer_id.integer'  => 'L’identifiant de l’émetteur doit être un entier.',
            'issuer_id.exists'   => 'L’émetteur sélectionné est introuvable.',

            'subject.string'   => 'L’objet doit être une chaîne de caractères.',

            'issue_date.date'              => 'La date d’émission doit être une date valide.',
            'issue_date.before_or_equal'   => 'La date d’émission ne peut pas être postérieure à aujourd’hui.',

            'total_amount_ttc.numeric'  => 'Le montant TTC doit être numérique.',
            'total_amount_ttc.min'      => 'Le montant TTC doit être supérieur ou égal à 0.',

            'currency.in'       => 'La devise doit être MAD, EUR ou USD.',

            'payment_method.in' => 'Le mode de paiement doit être transfer, check ou cash.',

            'delivery_lead_time_days.integer' => 'Le délai de livraison doit être un entier.',
            'delivery_lead_time_days.min'     => 'Le délai de livraison doit être supérieur ou égal à 0.',

            'status.in' => 'Le statut doit être draft, in_review, approved ou cancelled.',

            'validated_by_procurement_manager.boolean' => 'Le champ validation responsable achats doit être booléen.',
            'validated_by_controlling.boolean'         => 'Le champ validation contrôle de gestion doit être booléen.',
            'validated_by_board.boolean'               => 'Le champ validation direction doit être booléen.',

            'purchase_request_id.integer'  => 'L’identifiant de la demande d’achat doit être un entier.',
            'purchase_request_id.exists'   => 'La demande d’achat sélectionnée est introuvable.',
        ];
    }
}
