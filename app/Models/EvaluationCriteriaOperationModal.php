<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationCriteriaOperationModal extends Model
{
    use SoftDeletes;

    protected $table = 'evaluation_criteria_operations';

    protected $fillable = [
        'criteria_id',
        'grid_evaluation_id',
        'title',
        'criteria_code',
        'grade_level',
        'evaluation_type',
        'weighting',
        'niveau_appreciation',
        'success_indicators',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'grade_level' => 'string',
        'evaluation_type' => 'string',
        'niveau_appreciation' => 'string',
    ];

    public function gridEvaluation(): BelongsTo
    {
        return $this->belongsTo(EvaluationGridOperation::class, 'grid_evaluation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
