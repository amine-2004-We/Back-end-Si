<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partner_name' => 'required|string|max:255|unique:partners,partner_name',
            'abbreviation' => 'nullable|string|max:5',

            'phone' => 'nullable|string|max:30',
            'email' => ['nullable','max:255',Rule::unique('partners','email')->whereNull('deleted_at')],
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:255',
             'partner_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',

            'nature_partner_id' => 'nullable|exists:nature_partners,id',
            'partner_type' => 'nullable|in:National,International',
            'structure_partner_id' => 'nullable|exists:structure_partners,id',

            'contact_people' => 'nullable|array',
            'contact_people.*.first_name' => 'required|string|max:255',
            'contact_people.*.first_name_arabic' => 'nullable|string|max:255',
            'contact_people.*.last_name_arabic' => 'nullable|string|max:255',
            'contact_people.*.last_name' => 'required|string|max:255',
            'contact_people.*.position' => 'required|string|max:255',
            'contact_people.*.email' => 'required|email|max:255|unique:contact_people,email',
            'contact_people.*.phone' => 'required|string|max:30',
            'contact_people.*.address' => 'nullable|string|max:255',

             'notes' => 'nullable|array',
             'notes.*.note' => 'required|string',
         ];
    }
}
