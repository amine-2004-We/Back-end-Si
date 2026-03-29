<?php

namespace App\Http\Requests;

use App\Models\ModuleEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteModuleEvaluationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Support both:
     * - ids[]=1&ids[]=2
     * - ids=1,2,3
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->ids ?? null)) {
            $raw = array_filter(array_map('trim', explode(',', (string) $this->ids)));
            $this->merge(['ids' => $raw]);
        }
    }

    public function rules(): array
    {
        return [
            'ids'     => ['required', 'array', 'min:1'],
            'ids.*'   => ['integer', 'distinct', Rule::exists('module_evaluations', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Veuillez sélectionner au moins une évaluation à supprimer.',
            'ids.array'    => 'Le champ ids doit être un tableau d’identifiants.',
            'ids.min'      => 'Veuillez sélectionner au moins une évaluation.',
            'ids.*.exists' => 'Une ou plusieurs évaluations n’existent pas.',
            'ids.*.distinct'=> 'Les identifiants ne doivent pas contenir de doublons.',
        ];
    }

    public function attributes(): array
    {
        return [
            'ids'   => 'identifiants',
        ];
    }
}
