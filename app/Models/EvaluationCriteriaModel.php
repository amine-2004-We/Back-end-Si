<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationCriteriaModel extends Model
{
    use SoftDeletes;
    protected $table = 'evaluation_criteria';
    public $fillable = [
        "criteria_code",
        "name",
        "evaluation_grid_id",
        "object_project",
        "object_partner",
        "description",
        "grading_scale",
        "weighting_criterion",
        "order",
        "comments",
        "created_by",
    ];

    /**
     * Get the user who created the phase.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
