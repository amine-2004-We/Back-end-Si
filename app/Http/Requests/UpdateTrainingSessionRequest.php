<?php

namespace App\Http\Requests;

class UpdateTrainingSessionRequest extends StoreTrainingSessionRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        $rules['attachments_to_delete'] = ['nullable', 'array'];
        $rules['attachments_to_delete.*'] = ['integer', 'exists:training_session_attachments,id'];

        return $rules;
    }
}