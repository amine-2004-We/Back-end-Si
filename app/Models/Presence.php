<?php

namespace App\Models;

use App\Observers\PresenceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PresenceObserver::class])]
class Presence extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'presence_id',
        'personable_id',
        'personable_type',
        'task_id',
        'event_date',
        'status',
        'arrival_time',
        'justification',
        'observations',
        'declared_by',
        'created_by',
    ];


    /**
     * @return MorphTo
     */
    public function personable(): MorphTo
    {
        return $this->morphTo();
    }


    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }


    /**
     * @return BelongsTo
     */
    public function declarer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declared_by');
    }


    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
