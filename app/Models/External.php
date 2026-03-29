<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 *class External
 */
class External extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'participant_id',
        'training_group_id',
        'external_identifier',
        'full_name',
        'organization',
        'phone',
        'email',
        'cin',
        'country',
        'address',
        'function',
        'pedagogical_remarks',
        'attachments',
        'created_by',
    ];


    /**
     * @return BelongsToMany
     */
    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'external_training')
                    ->withPivot('training_evaluation', 'satisfaction_evaluation')
                    ->withTimestamps();
    }


    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * @return BelongsTo
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany(ExternalAttachment::class, 'external_id');
    }


    /**
     * @return BelongsTo
     */
    public function trainingGroup()
    {
        return $this->belongsTo(\App\Models\TrainingGroup::class, 'training_group_id');
    }
}
