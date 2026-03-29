<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAtelier extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['task_id', 'theme', 'objectives', 'expected_participants_count'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
