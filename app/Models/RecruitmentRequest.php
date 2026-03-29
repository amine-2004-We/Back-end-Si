<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentRequest extends Model
{
    //
    use SoftDeletes;
    protected $table = 'recruitment_requests';
    protected $fillable = [
        'request_id',
        'department_id',
        'position_id',
        'number_of_positions',
        'recruitment_reason',
        'required_skills',
        'desired_start_date',
        'status',
        'replaced_collaborator_id',
        'replacement_reason',
        'exit_date',
        'province_id',
        'project_id',
        'job_file',
        'created_by'
    ];

    public function department()
    {
        return $this->belongsTo(Departement::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
       
    }

    public function replacedCollaborator()
    {
        return $this->belongsTo(Collaborator::class, 'replaced_collaborator_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
