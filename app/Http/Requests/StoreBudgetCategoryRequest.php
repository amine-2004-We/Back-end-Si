<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetCategoryRequest extends FormRequest
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
        return [
            'code' => [
                'required',
                'string',
                Rule::unique('budget_categories', 'code')->whereNull('deleted_at'),
            ],
            'label' => [
                'required',
                'string',
                Rule::unique('budget_categories', 'label')->whereNull('deleted_at'),
            ],
            'type' => 'required|string|in:Fonctionnement,Investissement',
            'budgetary_area' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est requis',
            'code.string' => 'Le code doit être une chaîne de caractères',
            'code.unique' => 'Le code existe déjà',
            'label.required' => 'Le libellé est requis',
            'label.string' => 'Le libellé doit être une chaîne de caractères',
            'label.unique' => 'Le libellé existe déjà',
            'type.required' => 'Le type est requis',
            'type.string' => 'Le type doit être une chaîne de caractères',
            'type.in' => 'Le type doit être Fonctionnement ou Investissement',
            'budgetary_area.required' => 'Le budgetaire est requis',
            'budgetary_area.string' => 'Le budgetaire doit être une chaîne de caractères',
        ];
    }
}
