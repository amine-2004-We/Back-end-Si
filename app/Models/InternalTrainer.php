<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class InternalTrainer
 */
class InternalTrainer extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'internal_trainers';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'collaborator_id',
        'trainer_id',
    ];

    /**
     * @return BelongsTo
     */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }

    /**
     * @return BelongsTo
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    
}
