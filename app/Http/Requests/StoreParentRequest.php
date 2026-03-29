<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     */
    protected function prepareForValidation(): void
    {
        $sex = $this->input('sex');
        if (is_string($sex)) {
            $map = [
                'male' => 'Masculin',
                'female' => 'Féminin',
            ];
            $sexNorm = $map[strtolower($sex)] ?? $sex;
            $this->merge(['sex' => $sexNorm]);
        }

        $beneficiaries = $this->input('beneficiaires', []);
        if (is_array($beneficiaries)) {
            $roleMap = [
                'father' => 'Père',
                'mother' => 'Mère',
                'legal_guardian' => 'Tuteur légal',
            ];
            foreach ($beneficiaries as $i => $b) {
                if (isset($b['legal_role']) && is_string($b['legal_role'])) {
                    $lr = strtolower($b['legal_role']);
                    $beneficiaries[$i]['legal_role'] = $roleMap[$lr] ?? $b['legal_role'];
                }
            }
            $this->merge(['beneficiaires' => $beneficiaries]);
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'last_name'        => ['required', 'string', 'max:255'],
            'first_name'       => ['required', 'string', 'max:255'],
            'gender'              => ['required', Rule::in(['male', 'female'])],
            'primary_phone'    => ['required', 'string', 'max:20'],
            'secondary_phone'  => ['nullable', 'string', 'max:20'],
            'address'          => ['required', 'string'],
            'cin'              => ['required', 'string', 'max:50', Rule::unique('parents', 'cin')->whereNull('deleted_at')],
            'remarks'          => ['nullable', 'string'],
            'legal_role'       => ['prohibited'],
            'beneficiaries'                => ['nullable', 'array'],
            'beneficiaries.*.id'           => ['required_with:beneficiaries.*.legal_role', 'integer', 'distinct', 'exists:beneficiaires,id'],
            'beneficiaries.*.legal_role'   => ['nullable', Rule::in(['father', 'mother','legal_guardian'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'last_name.required'       => 'Le nom de famille est requis.',
            'last_name.string'         => 'Le nom de famille doit être une chaîne de caractères.',
            'last_name.max'            => 'Le nom de famille ne doit pas dépasser 255 caractères.',

            'first_name.required'      => 'Le prénom est requis.',
            'first_name.string'        => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max'           => 'Le prénom ne doit pas dépasser 255 caractères.',

            'gender.required'             => 'Le sexe est requis.',
            'gender.in'                   => 'Le sexe doit être Masculin ou Féminin.',

            'primary_phone.required'   => 'Le numéro de téléphone principal est requis.',
            'primary_phone.string'     => 'Le numéro de téléphone principal doit être une chaîne de caractères.',
            'primary_phone.max'        => 'Le numéro de téléphone principal ne doit pas dépasser 20 caractères.',

            'secondary_phone.string'   => 'Le numéro de téléphone secondaire doit être une chaîne de caractères.',
            'secondary_phone.max'      => 'Le numéro de téléphone secondaire ne doit pas dépasser 20 caractères.',

            'address.required'         => 'L\'adresse est requise.',
            'address.string'           => 'L\'adresse doit être une chaîne de caractères.',

            'cin.required'             => 'Le CIN est requis.',
            'cin.string'               => 'Le CIN doit être une chaîne de caractères.',
            'cin.max'                  => 'Le CIN ne doit pas dépasser 50 caractères.',
            'cin.unique'               => 'Ce CIN est déjà utilisé par un parent actif.',

            'remarks.string'           => 'Les remarques doivent être une chaîne de caractères.',

            'legal_role.prohibited'    => 'Le rôle légal ne peut plus être défini au niveau du parent. Utilisez le tableau beneficiaries.*.legal_role.',

            'beneficiaries.array'                      => 'Le champ beneficiaires doit être un tableau.',
            'beneficiaries.*.id.required_with'         => 'L\'identifiant du bénéficiaire est requis lorsque le rôle légal est fourni.',
            'beneficiaries.*.id.integer'               => 'L\'identifiant du bénéficiaire doit être un entier.',
            'beneficiaries.*.id.distinct'              => 'Chaque bénéficiaire doit être unique.',
            'beneficiaries.*.id.exists'                => 'Le bénéficiaire sélectionné est introuvable.',
            'beneficiaries.*.legal_role.in'            => 'Le rôle légal doit être Père, Mère ou Tuteur légal.',
        ];
    }
}
