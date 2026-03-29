<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize boolean-ish inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('remove_existing')) {
            $this->merge([
                'remove_existing' => filter_var(
                    $this->input('remove_existing'),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // One of these must be present:
            'attachment_path' => [
                'required_without:remove_existing',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240', // 10 MB
            ],
            'remove_existing' => [
                'required_without:attachment_path',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment_path.required_without' => 'Veuillez fournir un fichier ou indiquer la suppression de l’existant.',
            'attachment_path.file'            => 'Le fichier est invalide.',
            'attachment_path.mimes'           => 'Formats acceptés : PDF, JPG, JPEG, PNG.',
            'attachment_path.max'             => 'La taille maximale du fichier est de 10 Mo.',
            'remove_existing.required_without'=> 'Spécifiez un fichier à téléverser ou demandez la suppression du fichier existant.',
            'remove_existing.boolean'         => 'Le champ remove_existing doit être vrai ou faux.',
        ];
    }
}
