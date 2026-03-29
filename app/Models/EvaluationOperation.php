<?php

namespace App\Models;

use App\Enums\EvaluationStatusOperation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationOperation extends Model
{
    use SoftDeletes;

    protected $table = 'evaluation_operations';

    protected $fillable = [
        'evaluation_code',
        'beneficiary_id',
        'session_id',
        'evaluator',
        'comment',
        'evaluation_status_operation',
        'created_by',
        'attachment'
    ];

    protected $casts = [
        'evaluation_status_operation' => EvaluationStatusOperation::class,
    ];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }
    public function session(): BelongsTo
    {
        return $this->belongsTo(TaskEvaluation::class, 'session_id');
    }
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'evaluator');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteriaScores(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationGridCriteriaOperationModel::class , 'evaluation_grid_criteria');
    }
}
