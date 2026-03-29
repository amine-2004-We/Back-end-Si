<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    protected $table = 'leave';

    protected $fillable = [
        'collaborator_id',
        'reason',
        'leave_type_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'nbr_days',
        'is_full_day',
        'status',
    ];
    public function LeaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class, 'collaborator_id');
    }
}
