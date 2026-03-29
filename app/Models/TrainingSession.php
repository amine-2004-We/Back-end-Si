<?php

namespace App\Models;

use App\Observers\TrainingSessionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *class TrainingSession
 */
#[ObservedBy([TrainingSessionObserver::class])]
class TrainingSession extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'session_identifier',
        'module_id',
        'training_id',
        'training_group_id',
        'animator_type',
        'animator_id',
        'session_date',
        'start_time',
        'end_time',
        'planned_duration_hours',
        'site_id',
        'session_type',
        'presence_registered',
        'observations',
        'status',
        'created_by_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'session_date' => 'date',
        'presence_registered' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * @return BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    /**
     * @return BelongsTo
     */
    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    /**
     * @return MorphTo
     */
    public function animator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TrainingSessionAttachment::class);
    }
}
