<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderTermsFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_file'      => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'], 
            'remove_existing' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_file.file'   => 'Le fichier des conditions doit être un fichier valide.',
            'terms_file.mimes'  => 'Le fichier doit être au format PDF, DOC ou DOCX.',
            'terms_file.max'    => 'Le fichier ne doit pas dépasser 10 Mo.',
            'remove_existing.boolean' => 'Le champ remove_existing doit être booléen.',
        ];
    }

   
    protected function prepareForValidation(): void
    {
        if ($this->has('remove_existing')) {
            $this->merge([
                'remove_existing' => filter_var($this->input('remove_existing'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $hasFile = $this->file('terms_file') !== null;
            $wantsRemove = (bool) $this->input('remove_existing', false);

            if (!$hasFile && !$wantsRemove) {
                $v->errors()->add('terms_file', 'Veuillez fournir un fichier ou indiquer remove_existing=true.');
            }
        });
    }
}
