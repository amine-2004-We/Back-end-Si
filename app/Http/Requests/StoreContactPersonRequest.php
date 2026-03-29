<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\OriginalChannelEnum;
use App\Enums\ContactStatusEnum;

class StoreContactPersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contactId = $this->route('contact')?->id;
        
        return [
            'last_name'         => 'nullable|string|max:255',
            'first_name'        => 'nullable|string|max:255',
            'last_name_arabic'  => 'nullable|string|max:255',
            'first_name_arabic' => 'nullable|string|max:255',
            'email'             => 'required|email|max:255|unique:contact_people,email' . ($contactId ? ",$contactId" : ''),
            'phone'             => 'nullable|string|max:50|unique:contact_people,phone' . ($contactId ? ",$contactId" : ''),
            'organisation'      => 'nullable|string|max:255',
            'position'          => 'nullable|string|max:255',
            'original_channel'  => ['required', new Enum(OriginalChannelEnum::class)],
            'acquisition_date'  => 'required|date',
            'contact_status'    => ['required', new Enum(ContactStatusEnum::class)],
            'option'            => 'required|boolean',
            'consent_date'      => 'nullable|date',
            'companion'         => 'nullable|string|max:255',
            'tags'              => 'nullable|string|max:255',
            'api'               => 'required|boolean',

        ];
    }

    public function messages(): array
    {
        return [
            'last_name.string'              => 'Le nom doit être une chaîne de caractères.',
            'last_name.max'                 => 'Le nom ne doit pas dépasser 255 caractères.',
            
            'first_name.string'             => 'Le prénom doit être une chaîne de caractères.',
            'first_name.max'                => 'Le prénom ne doit pas dépasser 255 caractères.',
            
            'last_name_arabic.string'       => 'Le nom en arabe doit être une chaîne de caractères.',
            'last_name_arabic.max'          => 'Le nom en arabe ne doit pas dépasser 255 caractères.',
            
            'first_name_arabic.string'      => 'Le prénom en arabe doit être une chaîne de caractères.',
            'first_name_arabic.max'         => 'Le prénom en arabe ne doit pas dépasser 255 caractères.',
            
            'email.required'                => 'L\'adresse e-mail est obligatoire.',
            'email.email'                   => 'L\'adresse e-mail doit être une adresse valide.',
            'email.max'                     => 'L\'adresse e-mail ne doit pas dépasser 255 caractères.',
            'email.unique'                  => 'Cette adresse e-mail est déjà utilisée.',
            
            'phone.string'                  => 'Le téléphone doit être une chaîne de caractères.',
            'phone.max'                     => 'Le téléphone ne doit pas dépasser 50 caractères.',
            'phone.unique'                  => 'Ce numéro de téléphone est déjà utilisé.',
            
            'organisation.string'           => 'L\'organisation doit être une chaîne de caractères.',
            'organisation.max'              => 'L\'organisation ne doit pas dépasser 255 caractères.',
            
            'position.string'               => 'Le poste doit être une chaîne de caractères.',
            'position.max'                  => 'Le poste ne doit pas dépasser 255 caractères.',
            
            'original_channel.required'     => 'Le canal d\'acquisition est obligatoire.',
            
            'acquisition_date.required'     => 'La date d\'acquisition est obligatoire.',
            'acquisition_date.date'         => 'La date d\'acquisition doit être une date valide.',
            
            'contact_status.required'       => 'Le statut du contact est obligatoire.',
            
            'option.required'               => 'L\'option est obligatoire.',
            'option.boolean'                => 'L\'option doit être vrai ou faux.',
            
            'consent_date.date'             => 'La date de consentement doit être une date valide.',
            
            'companion.string'              => 'Le compagnon doit être une chaîne de caractères.',
            'companion.max'                 => 'Le compagnon ne doit pas dépasser 255 caractères.',
            
            'tags.string'                   => 'Les tags doivent être une chaîne de caractères.',
            'tags.max'                      => 'Les tags ne doivent pas dépasser 255 caractères.',
            
            'api.required'                  => 'L\'indicateur API est obligatoire.',
            'api.boolean'                   => 'L\'indicateur API doit être vrai ou faux.',
        ];
    }

    public function attributes(): array
    {
        return [
            'last_name'         => 'nom',
            'first_name'        => 'prénom',
            'last_name_arabic'  => 'nom en arabe',
            'first_name_arabic' => 'prénom en arabe',
            'email'             => 'adresse e-mail',
            'phone'             => 'téléphone',
            'organisation'      => 'organisation',
            'position'          => 'poste',
            'original_channel'  => 'canal d\'acquisition',
            'acquisition_date'  => 'date d\'acquisition',
            'contact_status'    => 'statut du contact',
            'option'            => 'option',
            'consent_date'      => 'date de consentement',
            'companion'         => 'compagnon',
            'tags'              => 'tags',
            'api'               => 'indicateur API',
        ];
    }
}
