<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanType extends Model
{
    use SoftDeletes;
    protected $table = 'plan_type';
    protected $fillable = [
        'phase_id',
        'task_name',
        'previous_phases_id',
        'responsible_title',
        'order',
        'duration'
    ];
    protected $casts = [
        'responsible_title' => 'array',
    ];
    public function phase():BelongsTo{
        return $this->belongsTo(Phase::class, 'phase_id');
    }
    public function previousPhase():BelongsTo{
        return $this->belongsTo(Phase::class, 'previous_phases_id');
    }
}
