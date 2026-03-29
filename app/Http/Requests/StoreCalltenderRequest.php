<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalltenderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier' => 'required|string|max:255',
            'subject' => 'required|string|max:1000',
            'purchase_order_refs' => 'nullable|string|max:255',
            'calltender_type' => 'required|string|in:Projet,Pluriannuel,Cadre,Autre',
            'start_date' => 'required|date|before_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|in:MAD,EUR,USD',
            'conditions_path' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required|string|in:En préparation,Signé,Résilié,Clôturé',
            'signature_date' => 'nullable|date|required_if:status,Signé',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier.required' => 'Le fournisseur est obligatoire.',
            'supplier.string' => 'Le fournisseur doit être une chaîne de caractères.',
            'supplier.max' => 'Le nom du fournisseur ne doit pas dépasser :max caractères.',

            'subject.required' => 'L\'objet du marché est obligatoire.',
            'subject.string' => 'L\'objet du marché doit être une chaîne de caractères.',
            'subject.max' => 'L\'objet du marché ne doit pas dépasser :max caractères.',

            'purchase_order_refs.string' => 'La référence du bon de commande doit être une chaîne de caractères.',
            'purchase_order_refs.max' => 'La référence du bon de commande ne doit pas dépasser :max caractères.',

            'calltender_type.required' => 'Le type de marché est obligatoire.',
            'calltender_type.string' => 'Le type de marché doit être une chaîne de caractères.',
            'calltender_type.in' => 'Le type de marché sélectionné est invalide.',

            'start_date.required' => 'La date de début de validité est obligatoire.',
            'start_date.date' => 'La date de début de validité doit être une date valide.',
            'start_date.before_or_equal' => 'La date de début de validité ne peut pas être postérieure à la date du jour.',

            'end_date.required' => 'La date de fin de validité est obligatoire.',
            'end_date.date' => 'La date de fin de validité doit être une date valide.',
            'end_date.after_or_equal' => 'La date de fin de validité doit être égale ou postérieure à la date de début de validité.',

            'total_amount.required' => 'Le montant total du marché est obligatoire.',
            'total_amount.numeric' => 'Le montant total du marché doit être un nombre.',
            'total_amount.min' => 'Le montant total du marché doit être au moins :min.',

            'currency.required' => 'La devise est obligatoire.',
            'currency.string' => 'La devise doit être une chaîne de caractères.',
            'currency.in' => 'La devise sélectionnée est invalide.',

            'conditions_path.required' => 'Le document des conditions contractuelles est obligatoire.',
            'conditions_path.file' => 'Les conditions contractuelles doivent être un fichier.',
            'conditions_path.mimes' => 'Le document des conditions contractuelles doit être au format PDF.',
            'conditions_path.max' => 'Le document des conditions contractuelles ne doit pas dépasser :max kilo-octets (2 Mo).',

            'status.required' => 'Le statut du marché est obligatoire.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut sélectionné est invalide.',

            'signature_date.nullable' => 'La date de signature peut être vide.',
            'signature_date.date' => 'La date de signature doit être une date valide.',
            'signature_date.required_if' => 'La date de signature est obligatoire si le statut est "Signé".',

            'notes.string' => 'Les observations doivent être une chaîne de caractères.',
            'notes.max' => 'Les observations ne doivent pas dépasser :max caractères.',
        ];
    }
}