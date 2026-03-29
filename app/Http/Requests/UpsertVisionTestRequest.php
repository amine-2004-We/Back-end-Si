<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertVisionTestRequest extends FormRequest
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
            'right_eye'       => ['required', 'integer', 'min:1', 'max:10'],
            'left_eye'        => ['required', 'integer', 'min:1', 'max:10'],
            'refer_to_center' => ['required', 'boolean'],
            'observations'    => ['required', 'string', 'max:1000'],
        ];
    }
}
