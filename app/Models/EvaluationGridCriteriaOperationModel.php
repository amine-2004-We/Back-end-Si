<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationGridCriteriaOperationModel extends Model
{
    protected $table = 'evaluation_grid_criteria_operations';
    protected $fillable = [
        'evaluation_id',
        'grid_id',
        'criteria_id',
        'score',
    ];

    protected $casts = [
        'score' => 'float',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(EvaluationOperation::class);
    }

    public function grid(): BelongsTo
    {
        return $this->belongsTo(EvaluationGridOperation::class, 'grid_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteriaOperationModal::class, 'criteria_id');
    }
}
