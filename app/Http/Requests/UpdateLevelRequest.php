<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Level; // Importez le modèle Level

class UpdateLevelRequest extends FormRequest
{
    /**
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $levelId = $this->route('level'); // Récupère l'ID du niveau depuis la route
        $level = $this->route('level') instanceof Level ? $this->route('level') : Level::withTrashed()->find($levelId);
        $minAgeForValidation = $this->has('min_age')
            ? $this->input('min_age')
            : ($level ? $level->min_age : 0);
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('levels', 'code')->ignore($levelId)],
            'cycle_id' => ['sometimes', 'required', 'integer', 'exists:cycles,id'],
            'order' => ['sometimes', 'required', 'integer', 'min:0'],
            'min_age' => ['sometimes', 'required', 'integer', 'min:0'],
            'max_age' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                "gte:{$minAgeForValidation}",
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('order')) {
            $this->merge(['order' => (int) $this->input('order')]);
        }
        if ($this->has('min_age')) {
            $this->merge(['min_age' => (int) $this->input('min_age')]);
        }
        if ($this->has('max_age')) {
            $this->merge(['max_age' => (int) $this->input('max_age')]);
        }
    }
}