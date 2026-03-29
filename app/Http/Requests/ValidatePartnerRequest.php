<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidatePartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
        $partnerId = $this->route('partner');

        return [
            'partner_name' => ['required', 'string', 'max:255'],
            'abbreviation' => 'nullable|string|max:5',
            'phone' => 'required|string|max:30',
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('partners', 'email')->ignore($partnerId)->whereNull('deleted_at'),
            ],
            'address' => 'required|string',
            'country' => 'required|string|max:255',
            'partner_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'partner_type' => 'required|in:National,International',
            'nature_partner_id' => 'required|exists:nature_partners,id',
            'structure_partner_id' => 'required|exists:structure_partners,id',
            'status_id' => 'required|exists:status_partners,id',
            'closure_reason' => [
                'nullable',
                'string',
                Rule::requiredIf(function () {
                    $clotureStatus = \App\Models\StatusPartner::where('name', 'Clôturé')->first();
                    return $this->input('status_id') == $clotureStatus?->id;
                })
            ],

            'contact_people' => 'required|array|min:1',
            'contact_people.*.id' => 'nullable|exists:contact_people,id',
            'contact_people.*.first_name' => 'required|string|max:255',
            'contact_people.*.last_name' => 'required|string|max:255',
            'contact_people.*.position' => 'required|string|max:255',
            'contact_people.*.email' => 'required|email|max:255',
            'contact_people.*.phone' => 'required|string|max:30',
            'contact_people.*.address' => 'nullable|string|max:255',

            'notes' => 'nullable|array',
            'notes.*.note' => 'required|string',
        ];
    }
}

