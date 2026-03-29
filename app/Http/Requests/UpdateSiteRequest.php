<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Seuls le Directeur des opérations, Chefs de projets / Responsable régional peuvent modifier un Site.
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'internal_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('sites')->ignore($this->site->id)],
            'type' => ['sometimes', 'required', 'string', Rule::in(['Rural', 'Urbain', 'Semi-urbain'])],
            'commune' => ['sometimes', 'required', 'string', 'max:255'],
            'province' => ['sometimes', 'required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'country' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['Actif', 'Fermé', 'En pause', 'Archivé'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'observations' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
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
            'commune.required' => 'La commune est obligatoire.',
            'province.required' => 'La province est obligatoire.',
            'region.required' => 'La région est obligatoire.',
            'country_id.required' => 'Le pays est obligatoire.', 
            'country_id.exists' => 'Le pays sélectionné est invalide.',
            'start_date.required' => 'La date de démarrage est obligatoire.',
            'start_date.date_format' => 'La date de démarrage doit être au format YYYY-MM-DD.',
            'status.required' => 'Le statut du site est obligatoire.',
            'status.in' => 'Le statut du site sélectionné est invalide.',
            'latitude.numeric' => 'La latitude doit être un nombre.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.numeric' => 'La longitude doit être un nombre.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];
    }
}
