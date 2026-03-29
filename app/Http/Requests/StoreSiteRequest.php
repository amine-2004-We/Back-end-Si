<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $uniqueRule = Rule::unique('sites', 'internal_code');
        if ($this->site) {
            $uniqueRule->ignore($this->site);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'internal_code' => ['required', 'string', 'max:255', $uniqueRule],
            'type' => ['required', 'string', Rule::in(['Rural', 'Urbain', 'Semi-urbain'])],
            'commune_id' => ['required','integer', 'exists:communes,id'],
            'douar_id' => ['nullable','integer', 'exists:douars,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', 'string', Rule::in(['Actif', 'Fermé', 'En pause', 'Archivé'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'observations' => ['nullable', 'string'],
        ];
    }

    /**
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du site est obligatoire.',
            'internal_code.required' => 'Le code interne du site est obligatoire.',
            'internal_code.unique' => 'Le code interne du site doit être unique.',
            'type.required' => 'Le type de site est obligatoire.',
            'type.in' => 'Le type de site sélectionné est invalide.',
            'commune_id.required' => 'La commune est obligatoire.',
            'commune_id.exists' => 'La commune sélectionnée est invalide.',
            'douar_id.required' => 'Le douar est obligatoire.',
            'douar_id.exists' => 'Le douar sélectionné est invalide.',
            'country_id.required' => 'Le pays est obligatoire.',
            'country_id.exists' => 'Le pays sélectionné est invalide.',
            'start_date.required' => 'La date de démarrage est obligatoire.',
            'start_date.date_format' => 'La date de démarrage doit être au format AAAA-MM-JJ.',
            'status.required' => 'Le statut du site est obligatoire.',
            'status.in' => 'Le statut du site sélectionné est invalide.',
            'latitude.required' => 'La latitude est obligatoire.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.required' => 'La longitude est obligatoire.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            // 'observations.required' => 'Le champ observations est obligatoire.',
        ];
    }
}
