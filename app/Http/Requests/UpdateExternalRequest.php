<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExternalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $externalId = $this->route('external')->id;
        return [
            'full_name' => 'sometimes|required|string|max:255',
            'training_group_id' => ['nullable', 'integer', 'exists:training_groups,id'],
            'organization' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => ['sometimes', 'required', 'email', Rule::unique('externals')->ignore($externalId)],
            'cin' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'function' => 'nullable|string|max:255',
            'pedagogical_remarks' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',

            'attachments_to_delete' => 'nullable|array',
            'attachments_to_delete.*' => 'integer|exists:external_attachments,id',

            'participant_details' => 'sometimes|array',
            'participant_details.comments' => 'nullable|string',
            'participant_details.insured' => 'sometimes|boolean',

            'trainings' => 'sometimes|array',
            'trainings.*.id' => 'sometimes|required|integer|exists:trainings,id',
            'trainings.*.training_evaluation' => ['nullable', 'string', Rule::in(['Réussite', 'Echec', 'A revoir'])],
            'trainings.*.satisfaction_evaluation' => ['nullable', 'string', Rule::in(['Oui', 'Non'])],
        ];
    }
}
