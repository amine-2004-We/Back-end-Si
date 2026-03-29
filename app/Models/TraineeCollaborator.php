<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TraineeCollaborator extends Model
{
    use SoftDeletes;

    protected $table = 'trainee_collaborators';
    protected $fillable = [
        'remarks',
        'participant_id',
        'collaborator_id',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }

    public function trainings()
    {
        return $this->belongsToMany(
            Training::class,
            'trainee_collaborator_training',
            'trainee_collaborator_id',
            'training_id'
        )
        ->withPivot(['training_evaluation_status', 'satisfaction_evaluation', 'registered_at'])
        ->withTimestamps();
    }

    public function trainingGroups()
    {
        return $this->belongsToMany(
            TrainingGroup::class,
            'collaborator_training_group',
            'collaborator_id',
            'training_group_id'
        )->withTimestamps();
    }
}
