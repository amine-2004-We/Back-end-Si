<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marche_id' => 'required|integer|exists:calltenders,id',
            'subject' => 'required|string|max:1000',
            'modification_nature' => 'required|string|in:Financier,Périmètre,Durée,Autre',
            'additional_amount' => 'nullable|numeric|min:0',
            'new_end_date' => 'nullable|date|after_or_equal:today',
            'document_path' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required|string|in:En préparation,Signé,Annulé',
            'signature_date' => 'required|date',
            'observations' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'marche_id.required' => 'Le marché associé est obligatoire.',
            'marche_id.integer' => 'Le marché associé doit être un entier.',
            'marche_id.exists' => 'Le marché associé sélectionné est invalide.',

            'subject.required' => 'L\'objet de l\'avenant est obligatoire.',
            'subject.string' => 'L\'objet de l\'avenant doit être une chaîne de caractères.',
            'subject.max' => 'L\'objet de l\'avenant ne doit pas dépasser :max caractères.',

            'modification_nature.required' => 'La nature de la modification est obligatoire.',
            'modification_nature.string' => 'La nature de la modification doit être une chaîne de caractères.',
            'modification_nature.in' => 'La nature de la modification sélectionnée est invalide.',

            'additional_amount.numeric' => 'Le montant additionnel doit être un nombre.',
            'additional_amount.min' => 'Le montant additionnel doit être au moins :min.',

            'new_end_date.date' => 'La nouvelle date de fin doit être une date valide.',
            'new_end_date.after_or_equal' => 'La nouvelle date de fin doit être égale ou postérieure à la date du jour.',

            'document_path.required' => 'Le document de l\'avenant signé est obligatoire.',
            'document_path.file' => 'Le document doit être un fichier.',
            'document_path.mimes' => 'Le document doit être au format PDF.',
            'document_path.max' => 'Le document ne doit pas dépasser :max kilo-octets (2 Mo).',

            'status.required' => 'Le statut de l\'avenant est obligatoire.',
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut sélectionné est invalide.',

            'signature_date.required' => 'La date de signature est obligatoire.',
            'signature_date.date' => 'La date de signature doit être une date valide.',

            'observations.string' => 'Les observations doivent être une chaîne de caractères.',
            'observations.max' => 'Les observations ne doivent pas dépasser :max caractères.',
        ];
    }
}

