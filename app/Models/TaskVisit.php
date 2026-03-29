<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskVisit extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = true;
    /**
     * @var string[]
     */
    protected $fillable = ['task_id', 'subject', 'observed_collaborator_id', 'objectives', 'observation_grid_info'];

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
    public function observedCollaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'observed_collaborator_id');
    }
}
