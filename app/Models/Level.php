<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'level_id',
        'title',
        'code',
        'cycle_id',
        'order',
        'min_age',
        'max_age',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycle_id');
    }

    /**
     * Get the user that created the level.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine if the level is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->deleted_at === null;
    }

    /**
     * Determine if the level is inactive.
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->deleted_at !== null;
    }
}
