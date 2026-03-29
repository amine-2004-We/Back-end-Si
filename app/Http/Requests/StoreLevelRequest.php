<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLevelRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:levels,code'],
            'cycle_id' => ['required', 'integer', 'exists:cycles,id'],
            'order' => ['required', 'integer', 'min:0'],
            'min_age' => ['required', 'integer', 'min:0'],
            'max_age' => ['required', 'integer', 'min:0', 'gte:min_age'], // max_age doit être >= min_age
        ];
    }
}