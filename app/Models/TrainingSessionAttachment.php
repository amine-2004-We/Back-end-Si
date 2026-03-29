<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSessionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_session_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    /**
     * Get the training session that this attachment belongs to.
     */
    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }
}