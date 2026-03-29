<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaskMeeting extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = true;
    /**
     * @var string[]
     */
    protected $fillable = ['task_id', 'theme', 'expected_participants_count', 'objectives', 'distributed_documents'];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->Task(Activity::class);
    }

    /**
     * @return BelongsToMany
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'task_meeting_parent', 'task_meeting_id', 'parent_id');
    }
}
