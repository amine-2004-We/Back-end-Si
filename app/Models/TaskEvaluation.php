<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskEvaluation extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = true;
    /**
     * @var string[]
     */
    protected $fillable = [
        'task_id',
        'evaluated_beneficiary_id',
        'linked_evaluation_session_id',
        'evaluation_grid_code',
        'learning_domain',
        'targeted_competency',
        'achieved_level',
        'qualitative_comment',
        'participation_status'
    ];

    /**
     * @return BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * @return BelongsTo
     */
    public function evaluatedBeneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'evaluated_beneficiary_id');
    }
}
