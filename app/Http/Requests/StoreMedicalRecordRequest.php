<?php

namespace App\Http\Requests;

use App\Enums\MedicalRecordsTypes;
use App\Enums\ProcessingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'collaborator_id' => 'nullable|exists:collaborators,id',
            'medical_records_types' => ['required', new Enum(MedicalRecordsTypes::class)],
            'consultation_date' => ['required', 'date', 'before_or_equal:today'],
            'filing_date' => ['required', 'date', 'after_or_equal:consultation_date'],
            'sent_insurance_date' => ['required', 'date'],
            'document_issuer' => ['nullable', 'string', 'max:255'],
            'declaration_number' => ['required', 'string', 'max:255'],
            'processing_status' => ['required', new Enum(ProcessingStatus::class)],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'comment' => ['nullable', 'string', 'max:255'],
            'refusal_reason' => ['nullable', 'string', 'max:255'],
            'committed_amount'=>['required','numeric'],
            'received_amount'=>['nullable','numeric'],
        ];
    }

    public function messages()
    {
        return [
            'consultation_date.before_or_equal' => 'La date de consultation doit être inférieure ou égale à aujourd’hui.',
            'filing_date.after_or_equal' => 'La date de dépôt doit être supérieure ou égale à la date de consultation.',
            'sent_insurance_date.required' => 'La date d’envoi à l’assurance doit être remplie.',
        ];
    }
}
