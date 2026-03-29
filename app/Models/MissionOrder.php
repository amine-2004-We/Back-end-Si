<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * class MissionOrder
 */
class MissionOrder extends Model
{
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'mission_description',
        'advance_amount',
        'objective',
        'project_id',
        'collaborator_id',
        'start_date',
        'end_date',
        'mission_type',
        'status',
        'is_advance_requested',
        'province_id',
    ];

    /**
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo
     */
    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }

    /**
     * @return BelongsTo
     */
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
