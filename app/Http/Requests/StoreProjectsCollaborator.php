<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectsCollaborator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'projects' => 'required|array',
            'projects.*' => 'exists:projects,id',
        ];
    }
}
