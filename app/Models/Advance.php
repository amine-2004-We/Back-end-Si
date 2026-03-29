<?php

namespace App\Models;

use App\Enums\AdvanceTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    use SoftDeletes;

    protected $table = 'advances';

    protected $fillable = [
        'advance_code',
        'collaborator_id',
        'project_id',
        'advance_type',
        'advance_amount',
        'proof_expected',
        'proof_status',
        'created_by'
    ];

    protected $casts=[
        'advance_type'=>AdvanceTypeEnum::class
    ];

    /***
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /***
     * @return BelongsTo
     */
    public function collaborator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class);
    }

    /***
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
