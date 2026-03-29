<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * class UpdateProjectBankAccountRequest
 */
class UpdateProjectBankAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }



    public function rules(): array
    {
        return [
            'rib_iban' => [
                'required',
                'string',
                Rule::unique('project_bank_accounts', 'rib_iban')->ignore($this->route('project_bank_account'))->whereNull('deleted_at'),
            ],
            'account_title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_bank_accounts', 'account_title')->ignore($this->route('project_bank_account'))->whereNull('deleted_at'),
            ],
            'bank_id' => 'required|exists:banks,id',
            'agency' => 'nullable|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'opening_country' => 'required|string|max:255',
            'opening_date' => 'nullable|string|max:255',
            'supporting_document' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,svg',
            'status' => 'required|string|max:255',
            'comments' => 'nullable|string|max:255',
        ];
    }

}
