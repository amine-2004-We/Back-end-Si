<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallForProject extends Model
{
    //
    use SoftDeletes;
    protected $table = 'calls_for_projects';

    
    protected $fillable = [
        'title',
        'description',
        'responsible_id',
        'debut_date',
        'end_date',
        'estimated_budget',
        'status',
        'registration_link',
        'type',
        'selection_criteria',
        'required_documents',
        'sponsor_id',
        'submission_date',
        'submission_indicators',
        'expertise_areas',
        'target_audience',
        'project_duration',
        'potential_profiles',
        'required_resources',
        'work_plan',
        'created_by'


    ];

    public function responsible()
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sponsor()
    {
        return $this->belongsTo(Partner::class, 'sponsor_id');
    }

}
