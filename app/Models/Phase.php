<?php

namespace App\Models;

use App\Observers\PhaseObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PhaseObserver::class])]

class Phase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'phase_identifier',
        'name',
        'name_arabe',
        'tag',
        'status',
        'created_by',
    ];
    /**
     * Get the user who created the phase.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
