<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Group
 */
class Group extends Model
{
    use SoftDeletes;
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'name',
        'code',
        'class_id',

        'educator_id',
        'current_headcount',
        'target_capacity',
        'status',
        'start_date',
        'end_date',
        'remarks',
        'created_by',
        'level_id',
    ];

    /**
     * @return BelongsTo
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

  
    /**
     * @return BelongsTo
     */
    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function beneficiaries()
{
    return $this->belongsToMany(Beneficiary::class, 'beneficiary_group', 'group_id', 'beneficiary_id')
                ->withTimestamps();
}

protected $casts = ['start_date'=>'datetime'
, 'end_date'=>'datetime'
];


    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


}
