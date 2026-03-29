<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TrainingGroup
 */
class TrainingGroup extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'training_groups';

    /**
     * @var array<string, string>
     */
    protected $fillable = [
        'group_id',
        'title',
        'target_size',
        'current_size',
        'remarks',
        'status',
        'created_by_id',
        'responsible_id',
        'training_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'target_size' => 'integer',
        'current_size' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return BelongsTo
     */
    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    /**
     * @return BelongsToMany
     */
    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(
            Collaborator::class,
            'collaborator_training_group',
            'training_group_id',
            'collaborator_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(
            Candidate::class,
            'candidate_training_group',
            'training_group_id',
            'candidate_id'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function externals(): BelongsToMany
    {
        return $this->belongsToMany(
            External::class,
            'external_training_group',
            'training_group_id',
            'external_id'
        )->withTimestamps();
    }

public function responsible()
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }
}
