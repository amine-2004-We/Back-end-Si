<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ExpenseNote;

class BulkDeleteExpenseNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => [
                'integer',
                'distinct',
                Rule::exists((new ExpenseNote)->getTable(), 'id'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'ids'   => 'identifiants',
            'ids.*' => 'identifiant',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'  => 'La liste des :attribute est requise.',
            'ids.array'     => 'La liste des :attribute doit être un tableau.',
            'ids.min'       => 'Au moins un :attribute doit être fourni.',
            'ids.*.integer' => 'Chaque :attribute doit être un identifiant numérique.',
            'ids.*.distinct'=> 'Les :attribute ne doivent pas contenir de doublons.',
            'ids.*.exists'  => 'Un :attribute fourni est introuvable.',
        ];
    }
}
