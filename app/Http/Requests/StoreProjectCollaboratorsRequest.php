<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectCollaboratorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'collaborators' => 'required|array',
            'collaborators.*' => 'exists:collaborators,id',
        ];
    }
}
