<?php

namespace App\Http\Requests;

/**
 * The Update request extends the Store request to reuse its dynamic validation logic.
 */
class UpdateActivityRequest extends StoreActivityRequest
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
        $rules = parent::rules();

        $rules['attachments_to_delete'] = ['nullable', 'array'];
        $rules['attachments_to_delete.*'] = ['integer', 'exists:activity_attachments,id'];

        return $rules;
    }
}
