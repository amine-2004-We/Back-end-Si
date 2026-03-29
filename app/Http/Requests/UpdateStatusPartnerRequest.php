<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $statusId = $this->route('status_partner')->id;
        return [
            'name' => 'required|string|max:255|unique:status_partners,name,' . $statusId,
        ];
    }
}