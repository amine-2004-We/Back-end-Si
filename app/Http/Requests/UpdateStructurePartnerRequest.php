<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStructurePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $structureId = $this->route('structure_partner')->id;
        return [
            'name' => 'required|string|max:255|unique:structure_partners,name,' . $structureId,
        ];
    }
}