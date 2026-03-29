<?php

namespace App\Http\Requests;

use App\Enums\MedicalRecordsTypes;
use App\Enums\ProcessingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;


class UpdateMedicalRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'collaborator_id' => 'sometimes|exists:collaborators,id',
            'medical_records_types' => ['sometimes', new Enum(MedicalRecordsTypes::class)],
            'consultation_date' => ['sometimes', 'date'],
            'filing_date' => ['sometimes', 'date'],
            'sent_insurance_date' => ['sometimes', 'date'],
            'document_issuer' => ['nullable', 'string', 'max:255'],
            'declaration_number' => ['sometimes', 'string', 'max:255'],
            'processing_status' => ['sometimes', new Enum(ProcessingStatus::class)],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'comment' => ['nullable', 'string', 'max:255'],
            'refusal_reason' => ['nullable', 'string', 'max:255'],
            'committed_amount'=>['required','numeric'],
            'received_amount'=>['nullable','numeric'],
        ];
    }
}
