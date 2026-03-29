<?php

namespace App\Http\Requests;

use App\Models\StatusPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $partnerId = $this->route('partner');
        $clotureStatusId = StatusPartner::where('name', 'Clôturé')->value('id');

        $closureReasonRule = Rule::requiredIf(
            $this->input('status_id') == $clotureStatusId
        );

        return [
            'partner_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('partners', 'partner_name')->ignore($partnerId)->whereNull('deleted_at'),
            ],
            'abbreviation' => 'sometimes|required|string|max:5',
            'phone' => 'nullable|string|max:30',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('partners', 'email')->ignore($partnerId)->whereNull('deleted_at'),
            ],
            'address' => 'nullable|string',
            'country' => 'sometimes|required|string|max:255',

            'partner_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'nature_partner_id' => 'sometimes|required|exists:nature_partners,id',
            'partner_type' => 'sometimes|required|in:National,International',
            'structure_partner_id' => 'sometimes|required|exists:structure_partners,id',
            'status_id' => 'sometimes|required|exists:status_partners,id',
            'closure_reason' => [
                'nullable',
                'string',
                $closureReasonRule,
            ],

            'contact_people' => 'sometimes|required|array|min:1',
            'contact_people.*.id' => 'nullable|exists:contact_people,id',
            'contact_people.*.first_name' => 'required|string|max:255',
            'contact_people.*.last_name' => 'required|string|max:255',
            'contact_people.*.position' => 'required|string|max:255',
            'contact_people.*.email' => ['required', 'email', 'max:255'],
            'contact_people.*.phone' => 'required|string|max:30',
            'contact_people.*.address' => 'nullable|string|max:255',

            'notes' => 'sometimes|nullable|array',
            'notes.*.id' => 'nullable|exists:partner_notes,id',
            'notes.*.note' => 'required|string',
        ];
    }
}
