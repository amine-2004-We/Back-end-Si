<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Training
 */
class Training extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'trainings';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'training_type',
        'responsible_id',
        'start_date',
        'end_date',
        'status',
        'target_audience',
        'cabinet_id',
        'attachments',
        'notes',
        'created_by_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'attachments' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function responsible(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }

    /**
     * @return BelongsTo
     */
    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function traineeCollaborators(): BelongsToMany
    {
        return $this->belongsToMany(
            TraineeCollaborator::class,
            'trainee_collaborator_training',
            'training_id',
            'trainee_collaborator_id'
        )
        ->withPivot(['training_evaluation', 'satisfaction_evaluation', 'registered_at'])
        ->withTimestamps();
    }

    public function modules():HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function trainingGroups():HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }
}
