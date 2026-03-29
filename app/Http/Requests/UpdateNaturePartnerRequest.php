<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNaturePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $natureId = $this->route('nature_partner')->id;
        return [
            'name' => 'required|string|max:255|unique:nature_partners,name,' . $natureId,
        ];
    }
}