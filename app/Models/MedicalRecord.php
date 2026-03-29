<?php

namespace App\Models;

use App\Enums\MedicalRecordsTypes;
use App\Enums\ProcessingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use SoftDeletes;
    protected $table = 'medical_records';

    protected $fillable = [
        'collaborator_id',
        'medical_records_types',
        'consultation_date',
        'filing_date',
        'sent_insurance_date',
        'document_issuer',
        'declaration_number',
        'processing_status',
        'attachment',
        'comment',
        'committed_amount',
        'received_amount',
        'refusal_reason',
        'created_by'
    ];

    protected $casts = [
        'medical_records_types' => MedicalRecordsTypes::class,
        'processing_status' => ProcessingStatus::class,
    ];

    public function collaborator():belongsTo
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
