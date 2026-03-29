<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    //
    use SoftDeletes;

    protected $table = 'reports';
    protected $fillable = [
        'type',
        'task_id',
        'title',
        'event_date',
        'author_id',
        'summary',
        'positive_points',
        'recommendations',
        'attachment_path',
        'status',
        'creator_id',
       
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function author()
    {
        return $this->belongsTo(Collaborator::class, 'author_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

}
