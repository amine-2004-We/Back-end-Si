<?php

namespace App\Models;

use App\Enums\EvaluationStatusEnum;
use App\Enums\EvaluationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use SoftDeletes;

    protected $table = 'evaluations';

    protected $fillable = [
        'evaluation_code',
        'object_project',
        'object_partner',
        'evaluation_type',
        'evaluation_date',
        'evaluation_period_start_date',
        'evaluation_period_end_date',
        'evaluator_id',
        'comment',
        'evaluation_status',
        'notification',
        'criteria_scores',
        'created_by',
        'attachment'
    ];

    protected $casts = [
        'evaluation_type' => EvaluationTypeEnum::class,
        'evaluation_status' => EvaluationStatusEnum::class,
    ];

    // Relations
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'object_project');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'object_partner');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'evaluator_id');
    }

    public function notificationReceiver(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'notification');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteriaScores(): BelongsToMany
    {
        return $this->belongsToMany(EvaluationGridCriteriaModel::class , 'evaluation_grid_criteria');
    }
}
