<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationGridCriteriaModel extends Model
{
    protected $table = 'evaluation_grid_criteria';

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
        return $this->belongsTo(Evaluation::class);
    }

    public function grid(): BelongsTo
    {
        return $this->belongsTo(EvaluationGridModel::class, 'grid_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteriaModel::class, 'criteria_id');
    }
}
