<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseNoteStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => ['required', Rule::in(['draft','in_review','approved','rejected'])],
            'comments' => ['nullable','string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $status = $this->input('status');

            if ($status === 'rejected' && !$this->filled('comments')) {
                $v->errors()->add('comments', 'Un commentaire est requis lors du rejet.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'status'   => 'statut',
            'comments' => 'commentaires',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le :attribute est requis.',
            'status.in'       => 'Le :attribute doit être l’une des valeurs: draft, in_review, approved, rejected.',
            'comments.string' => 'Les :attribute doivent être une chaîne de caractères.',
        ];
    }
}
