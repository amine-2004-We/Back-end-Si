<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
{
    /**
     * Autoriser la requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliser les données avant validation.
     * - Mettre start_time/end_time en MAJ pour matcher 'AM'/'PM'
     */
    protected function prepareForValidation(): void
    {
        $start = $this->input('start_time');
        $end   = $this->input('end_time');

        $this->merge([
            'start_time' => is_string($start) ? strtoupper($start) : $start,
            'end_time'   => is_string($end)   ? strtoupper($end)   : $end,
        ]);
    }

    /**
     * Règles de validation.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason'             => ['required', 'string', 'max:1000'],
            'collaborator_id'    => ['required', 'integer', Rule::exists('collaborators', 'id')],
            'absence_type'       => ['required', 'string', Rule::in(['Maladie', 'Éducatif', 'Administratif', 'événements familiaux', 'mesures disciplinaire'])],
            'absence_status'     => ['required', 'string', Rule::in(['Justifié', 'Non justifié', 'autorisé'])],

            'start_date'         => ['required', 'date'],
            // AM/PM au lieu de HH:MM
            'start_time'         => ['required', Rule::in(['AM', 'PM'])],

            'end_date'           => ['required', 'date', 'after_or_equal:start_date'],
            'end_time'           => ['required', Rule::in(['AM', 'PM'])],

            // On aligne avec l’UI (PDF/JPG/PNG) — retire doc/docx
            'justification_path' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Règles supplémentaires dépendantes (vérification métier).
     * - Même jour: PM -> AM interdit.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $endDate   = $this->input('end_date');
            $startTime = $this->input('start_time');
            $endTime   = $this->input('end_time');

            if ($startDate && $endDate && $startDate === $endDate) {
                if ($startTime === 'PM' && $endTime === 'AM') {
                    $validator->errors()->add('end_time', "Sur une même journée, l'heure de fin ne peut pas être AM si l'heure de début est PM.");
                }
            }
        });
    }

    /**
     * Messages d’erreur personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => "Le motif de l'absence est requis.",
            'reason.string'   => "Le motif de l'absence doit être une chaîne de caractères.",
            'reason.max'      => "Le motif de l'absence ne doit pas dépasser 1000 caractères.",

            'collaborator_id.required' => "L'identifiant du collaborateur est requis.",
            'collaborator_id.integer'  => "L'identifiant du collaborateur doit être un entier.",
            'collaborator_id.exists'   => "Le collaborateur spécifié n'existe pas.",

            'absence_type.required' => "Le type d'absence est requis.",
            'absence_type.string'   => "Le type d'absence doit être une chaîne de caractères.",
            'absence_type.in'       => "Le type d'absence sélectionné est invalide.",

            'absence_status.required' => "Le statut de l'absence est requis.",
            'absence_status.string'   => "Le statut de l'absence doit être une chaîne de caractères.",
            'absence_status.in'       => "Le statut de l'absence sélectionné est invalide.",

            'start_date.required' => 'La date de début est requise.',
            'start_date.date'     => 'La date de début doit être une date valide.',

            'start_time.required' => "L'heure de début est requise.",
            'start_time.in'       => "L'heure de début doit être 'AM' ou 'PM'.",

            'end_date.required'      => 'La date de fin est requise.',
            'end_date.date'          => 'La date de fin doit être une date valide.',
            'end_date.after_or_equal'=> 'La date de fin doit être égale ou postérieure à la date de début.',

            'end_time.required' => "L'heure de fin est requise.",
            'end_time.in'       => "L'heure de fin doit être 'AM' ou 'PM'.",

            'justification_path.file'  => 'La justification doit être un fichier valide.',
            'justification_path.mimes' => 'La justification doit être un fichier de type : pdf, jpg, jpeg, png.',
            'justification_path.max'   => 'La justification ne doit pas dépasser 5 Mo.',
        ];
    }
}
