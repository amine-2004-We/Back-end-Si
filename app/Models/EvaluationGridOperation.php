<?php

namespace App\Models;

use App\Enums\GridStatusEnum;
use App\Enums\NiveauAppreciationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationGridOperation extends Model
{
    use SoftDeletes;

    protected $table = 'evaluation_grid_operations';

    protected $fillable = [
        'grid_code',
        'title',
        'task_evaluation_id',
        'educational_area',
        'targeted_overall_skill',
        'sub_skill',
        'project_id',
        'program_id',
        'niveau_appreciation',
        'user_id',
        'grid_version',
        'grid_status',
        'attachment',
    ];

    protected $casts = [
        'niveau_appreciation' => NiveauAppreciationEnum::class,
        'grid_status' => GridStatusEnum::class,
    ];

    // Relationships
    public function taskEvaluation(): BelongsTo
    {
        return $this->belongsTo(TaskEvaluation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
