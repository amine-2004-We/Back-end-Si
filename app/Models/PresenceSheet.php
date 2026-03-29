<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresenceSheet extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'task_id',
        'event_date',
        'participants',
        'declared_by',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     * This automatically converts the JSON 'participants' column to an array.
     *
     * @var array
     */
    protected $casts = [
        'participants' => 'array',
        'event_date' => 'date',
    ];

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
