<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    //
    use SoftDeletes;
    protected $table = 'job_postings';
    protected $fillable = [
        'name',
        'description',
        'project_id',
        'position_id',
        'launch_date',
        'closing_date',
        'type',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
